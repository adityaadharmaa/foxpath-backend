<?php 

namespace App\Services\ApplicationDecision;

use App\Models\{
    InternshipApplication,
    Program
};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplicationDecisionService
{
    public function decideByQuota(int $programId)
    {
        DB::beginTransaction();

        try{
            $program = Program::findOrFail($programId);

            $applications = InternshipApplication::where('programs_id', $programId)
            ->where('status', 'scored')
            ->orderBy('rank')
            ->get();

            if ($applications->isEmpty())
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Scored applicantion not found.'
                ], 404);
            }

            foreach($applications as $index => $application)
            {
                if($index < $program->capacity){
                    $application->update([
                        'status' => 'accepted',
                        'admitted_at' => now(),
                        'decided_at' => now(),
                        'is_final' => true
                    ]);

                    $this->setPlacementDates($application, $program);

                } else {
                    $application->update([
                        'status' => 'rejected',
                        'decided_at' => now(),
                        'is_final' => true
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Applicants have been decided based on quota'
            ], 201);
        } catch ( \Exception $e )
        {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to decided applicants.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    private function setPlacementDates(InternshipApplication $application, Program $program)
    {
        $start  = Carbon::parse($application->admitted_at)->addDays(7);
        $end    = (clone $start)->addMonths($program->placement_duration_months);

        $application->update([
            'placement_start_at' => $start,
            'placement_end_at' => $end
        ]);
    }
}