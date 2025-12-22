<?php

namespace App\Services\InternshipApplication;
use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\Support\Facades\DB;

class InternshipApplicationService
{
    public function store(int $userId, int $programId)
    {
        DB::beginTransaction();
        try{
            $program = Program::where('id', $programId)
                ->where('is_active', true)
                ->first();

            if(!$program)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Program not available'
                ], 404);
            }

            $exists = InternshipApplication::where('programs_id', $programId)
                ->where('users_id', $userId)
                ->exists();

            if($exists)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already applied for this program'
                ], 409);
            }

            $application = InternshipApplication::create([
                'programs_id' => $programId,
                'users_id' => $userId,
                'status' => 'submitted',
                'submitted_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Application submitted successfully',
                'data' => [
                    'application' => $application
                    ]
            ], 201);
        } catch (\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit application',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function updateStatus(int $applicationId, string $status)
    {
        $application = InternshipApplication::find($applicationId);

        if (! $application) {
            return response()->json([
                'status' => 'error',
                'message' => 'Application not found.'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $application->status = $status;

            $now = now();

            match ($status) {
                'verified' => $application->verified_at = $now,
                'accepted' => $application->decided_at = $now,
                'rejected' => $application->decided_at = $now,
                default => null,
            };

            $application->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Application status updated successfully.',
                'data' => [
                    'application' => $application->fresh()
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update application status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}