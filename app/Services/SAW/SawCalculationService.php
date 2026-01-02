<?php

namespace App\Services\SAW;

use App\Models\{
    ApplicationScore,
    Criteria,
    InternshipApplication,
    Program
};
use Illuminate\Support\Facades\DB;

class SawCalculationService
{

    public function calculateByProgram(int $programId)
    {
        DB::beginTransaction();

        try{
            $applications = InternshipApplication::where('programs_id', $programId)
            ->where('status', 'verified')
            ->get();

            if($applications->isEmpty()){
                return response()->json([
                    'status' => 'error',
                    'message' => 'No verified applications found.'
                ], 404);
            }

            $criterias = Criteria::where('is_active', true)->get();

            $stats = [];

            foreach ($criterias as $criteria){
                $values = ApplicationScore::where('criterias_id', $criteria->id)
                    ->whereIn(
                        'internship_applications_id',
                        $applications->pluck('id')
                    )
                    ->pluck('value');

                $stats[$criteria->id] = [
                    'max' => $values->max(),
                    'min' => $values->min()
                ];
            }

            foreach ($applications as $application){
                $finalScore = 0;

                foreach ($criterias as $criteria){
                    $score = ApplicationScore::where([
                        'internship_applications_id' => $application->id,
                        'criterias_id' => $criteria->id
                    ])->first();

                    if (!$score || $score->value === null){
                        continue;
                    }

                    if ($criteria->type === 'benefit'){
                        $normalized = $score->value / $stats[$criteria->id]['max'];
                    } else {
                        $normalized = $stats[$criteria->id]['min'] / $score->value;
                    }

                    $weighted = $normalized * $criteria->weight;
                    $finalScore += $weighted;

                    $score->update([
                        'normalized_value' => round($normalized, 6),
                        'weighted_value' => round($weighted, 6)
                    ]);
                }

                $application->update([
                    'final_score' => round($finalScore, 6),
                    'scored_at' => now(),
                    'status' => 'scored'
                ]);
            }

            $ranked = InternshipApplication::where('programs_id', $programId)
                ->where('status', 'scored')
                ->orderByDesc('final_score')
                ->get();
            
            $rank = 1;
            foreach ($ranked as $app) {
                $app->update(['rank' => $rank++]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'SAW calculation completed successfully.'
            ], 201);
        } catch (\Exception $e)
        {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to calculate SAW.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.'
            ], 500);
        }
    }


    public function getCalculationDetails(int $programId)
    {
        try {
            $program = Program::find($programId);

            if(!$program)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Program not found.'
                ], 404);
            }

            $applications = InternshipApplication::with(['user.profile.activeEducation'])
                ->where('programs_id', $programId)
                ->whereIn('status', ['verified', 'scored', 'accepted', 'rejected'])
                ->get();

            if($applications->isEmpty())
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No verified applicants found for this program.'
                ], 404);
            }

            $criterias = Criteria::where('is_active', true)->get();

            if($criterias->isEmpty())
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Scoring criteria have not been set.'
                ], 400);
            }

            $rawScores = ApplicationScore::whereIn('internship_applications_id', $applications->pluck('id'))->get();

            $stats = [];
            foreach ($criterias as $criteria) {
                $values = $rawScores->where('criterias_id', $criteria->id)->pluck('value');

                if($values->isEmpty())
                {
                    $stats[$criteria->id] = ['max' => 0, 'min' => 0];
                } else {
                    $stats[$criteria->id] = [
                        'max' => $values->max(),
                        'min' => $values->min()
                    ];
                }
            }

            $decisionMatrix = [];
            $normalizationMatrix = [];
            $preferenceMatrix = [];
            $finalRanking = [];

            foreach($applications as $application){
                $totalFinalScore = 0;

                $rowX = [];
                $rowR = [];
                $rowV = [];

                $profile = $application->user->profile;
                $education = $profile ? $profile->activeEducation : null;

                $fullName = $profile ? $profile->full_name : $application->user->username;
                $level = $education ? $education->level : '-';
                $institution = $education ? $education->institution_name : '-';
                $major = $education ? $education->major : '-';

                $identityNumber = '-';
                if ($education) {
                    if ($level === 'mahasiswa') {
                        $identityNumber = $education->nim ?? '-';
                    } elseif ($level === 'siswa') {
                        $identityNumber = $education->nisn ?? '-';
                    }
                }

                foreach ($criterias as $criteria) {
                    $scoreRecord = $rawScores
                        ->where('internship_applications_id', $application->id)
                        ->whereIn('criterias_id', $criteria->id)
                        ->first();

                    $value = $scoreRecord ? (float) $scoreRecord->value : 0;

                    $rowX[] = [
                        'criteria_code' => $criteria->code,
                        'criteria_name' => $criteria->name,
                        'value' => $value
                    ];

                    $normalized = 0;
                    $max = $stats[$criteria->id]['max'];
                    $min = $stats[$criteria->id]['min'];
                    $type = strtolower($criteria->type);

                    if($max > 0) {
                        if ($type === 'benefit') {
                            $normalized = $value / $max;
                        } else if ($type === 'cost') {
                            $normalized = ($value == 0) ? 0 : ($min / $value);
                        }
                    }

                    $rowR[] = [
                        'criteria_code' => $criteria->code,
                        'value' => round($normalized, 4)
                    ];

                    $weight = (float) $criteria->weight;
                    $weightedValue = $normalized * $weight;

                    $rowV[] = [
                        'criteria_code' => $criteria->code,
                        'weight' => $weight,
                        'value' => round($weightedValue, 4)
                    ];

                    $totalFinalScore += $weightedValue;
                }

                $userData = [
                    'id' => $application->id,
                    'full_name' => $fullName,
                    'type' => ucfirst($level),
                    'identity_number' => $identityNumber,
                    'institution' => $institution,
                    'major' => $major
                ];

                $decisionMatrix[] = array_merge($userData, ['scores' => $rowX]);
                $normalizationMatrix[] = array_merge($userData, ['scores' => $rowR]);
                $preferenceMatrix[] = array_merge($userData, ['scores' => $rowV]);

                $finalRanking[] = array_merge($userData, [
                    'final_score' => round($totalFinalScore, 4)
                ]);
            }

            usort($finalRanking, fn($a, $b) => $b['final_score'] <=> $a['final_score']);

            foreach ($finalRanking as $index => $item) {
                $finalRanking[$index]['rank'] = $index + 1;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'SAW calculation details retrieved successfully.',
                'data' => [
                    'program_info' => [
                        'name' => $program->name,
                        'total_applicants' => $application->count()
                    ], 
                    'criteria_info' => $criterias->map(function($c){
                        return [
                            'code' => $c->code,
                            'name' => $c->name,
                            'type' => $c->type,
                            'weight' => $c->weight
                        ];
                    }),
                    'steps' => [
                        'matrix_x_decision' => $decisionMatrix,
                        'matrix_normalizes' => $normalizationMatrix,
                        'matrix_v_preference' => $preferenceMatrix
                    ],
                    'final_ranking' => $finalRanking
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve SAW calculation details.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.'
            ], 500);
        }
    }
}