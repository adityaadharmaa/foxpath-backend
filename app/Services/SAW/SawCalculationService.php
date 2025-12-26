<?php

namespace App\Services\SAW;

use App\Models\{
    ApplicationScore,
    Criteria,
    InternshipApplication
};
use Illuminate\Support\Facades\DB;

class SawCalculationService
{
    // public function calculate(int $programId)
    // {
    //     DB::beginTransaction();
    //     try{
    //         $applications = InternshipApplication::with([
    //             'user.profileEducation',
    //             'scores'
    //         ])
    //         ->where('programs_id', $programId)
    //         ->where('status', 'verified')
    //         ->where('is_final', false)
    //         ->get();

    //         if($applications->isEmpty())
    //         {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'No eligible applications found.'
    //             ], 422);
    //         }

    //         $criterias = Criteria::where('is_active', true)->get();

    //         $matrix = [];

    //         foreach ($applications as $app)
    //         {
    //             foreach($criterias as $criteria)
    //             {
    //                 $matrix[$criteria->id][$app->id] = 
    //                     $this->getCriteriaValue($criteria, $app);
    //             }   
    //         }

    //         $normalized = $this->normalized($matrix, $criterias);

    //         $results = [];

    //         foreach($applications as $app)
    //         {
    //             $total = 0;
                
    //             foreach($criterias as $criteria)
    //             {
    //                 $total +=
    //                     $normalized[$criteria->id][$app->id]
    //                     * $criteria->weight;
    //             }

    //             $results[] = [
    //                 'application' => $app,
    //                 'final_score' => round($total, 5),
    //             ];
    //         }

    //         usort($results, fn($a, $b) => 
    //             $b['final_score'] <=> $a['final_score']
    //         ); 

    //         foreach ($results as $rank => $row)
    //         {
    //             $row['application']->update([
    //                 'final_score' => $row['final_score'],
    //                 'rank' => $rank + 1,
    //                 'scored_at' => now()
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'SAW calculating completed.',
    //             'data' => $results
    //         ], 200);
    //     } catch (\Exception $e)
    //     {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to calculate SAW.',
    //             'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.'
    //         ], 500);
    //     }
    // }

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
}