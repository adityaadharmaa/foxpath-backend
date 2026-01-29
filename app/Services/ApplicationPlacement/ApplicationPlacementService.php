<?php

namespace App\Services\ApplicationPlacement;

use App\Models\InternshipApplication;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApplicationPlacementService
{
    public function overrideDates(
        int $applicationId,
        ?string $startDate,
        ?string $endDate,
        ?int $durationMonths
    ) {
        $application = InternshipApplication::with('program')->find($applicationId);

        if (!$application || $application->status !== 'accepted') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid application. Status must be Accepted.'
            ], 422);
        }



        // if ($application->status !== 'accepted') {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Application not accepted or not found',
        //         'current_status' => $application->status
        //     ], 422);
        // }

        DB::beginTransaction();

        try {
            $currentStart = $application->placement_start_at ? Carbon::parse($application->placement_start_at) : now();
            $start = $startDate ? Carbon::parse($startDate) : $currentStart;

            if ($endDate) {
                $end = Carbon::parse($endDate);
            } elseif ($durationMonths) {
                $end = (clone $start)->addMonths($durationMonths);
            } else {
                $end = (clone $start)->addMonths(
                    $application->program->placement_duration_months
                );
            }

            $application->update([
                'placement_start_at' => $start,
                'placement_end_at' => $end
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Placement dates updated successfully.',
                'data' => [
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                    'duration' => $start->diffInMonths($end) . 'Months'
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update placement dates.',
                'error' =>  config('app.debug') ? $e->getMessage() : 'Internal server error.'
            ], 500);
        }
    }
}
