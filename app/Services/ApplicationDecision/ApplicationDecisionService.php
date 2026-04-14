<?php

namespace App\Services\ApplicationDecision;

use App\Mail\ApplicationStatusMail;
use App\Models\{
    InternshipApplication,
    Program
};
use App\Notifications\ApplicationDecisionNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicationDecisionService
{
    public function decideByQuota(int $programId)
    {
        DB::beginTransaction();

        try {
            $program = Program::findOrFail($programId);

            $applications = InternshipApplication::with(['user.profile', 'program'])
                ->where('programs_id', $programId)
                ->where('status', 'calculated')
                ->whereNotNull('rank')
                ->orderBy('rank', 'asc')
                ->get();

            if ($applications->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No calculated applications found. Please run SAW calculation first.'
                ], 404);
            }

            foreach ($applications as $index => $application) {
                // if ($index < $program->capacity) {
                //     $application->update([
                //         'status' => 'accepted',
                //         'admitted_at' => now(),
                //         'decided_at' => now(),
                //         'is_final' => true
                //     ]);

                //     $this->setPlacementDates($application, $program);
                // } else {
                //     $application->update([
                //         'status' => 'rejected',
                //         'decided_at' => now(),
                //         'is_final' => true
                //     ]);
                // }

                $isAccepted = $index < $program->capacity;
                $status = $isAccepted ? 'accepted' : 'rejected';

                $updatData = [
                    'status' => $status,
                    'decided_at' => now(),
                    'is_final' => true
                ];

                if ($isAccepted) {
                    $updatData['admitted_at'] = now();
                }

                $application->update($updatData);

                if ($isAccepted) {
                    $this->setPlacementDates($application, $program);
                }

                try {
                    $delayInSeconds = $index * 5;
                    Mail::to($application->user->email)->later(
                        now()->addSeconds($delayInSeconds),
                        (new ApplicationStatusMail($application))->onQueue('emails')
                    );

                    $application->user->notify(new ApplicationDecisionNotification($application));
                } catch (\Exception $e) {
                    Log::error("Gagal kirim email ke {$application->user->email} : " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Applicants have been decided and notification emails have been sent.'
            ], 201);
        } catch (\Exception $e) {
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
        if ($program->cohort_starts_at) {
            $start = Carbon::parse($program->cohort_starts_at);
        } else {
            $start  = Carbon::parse($application->admitted_at)->addDays(7);
        }
        $end    = (clone $start)->addMonths($program->placement_duration_months);

        $application->update([
            'placement_start_at' => $start,
            'placement_end_at' => $end
        ]);
    }
}
