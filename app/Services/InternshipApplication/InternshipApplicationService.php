<?php

namespace App\Services\InternshipApplication;
use App\Models\InternshipApplication;
use App\Models\Profile;
use App\Models\Program;
use Illuminate\Support\Facades\DB;

class InternshipApplicationService
{
    public function store(int $userId, int $programId)
    {
        $profile = Profile::where('users_id', $userId)->first();

        if(!$profile){
            return response()->json([
                'status' => 'error',
                'message' => 'Please complete your profile before applying.'
            ], 422);
        }

        $requiredFields = [
            'applicant_type',
            'full_name',
            'date_of_birth',
            'phone',
            'address'
        ];

        foreach($requiredFields as $field)
        {
            if(!isset($profile->$field) || trim((string)$profile->$field) === ''){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your profile is incomplete.',
                    'missing_field' => $field
                ], 422);
            }
        }

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

            $activeExists = InternshipApplication::where('programs_id', $programId)
                ->where('users_id', $userId)
                ->where('is_final', false)
                ->exists();

            if($activeExists)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have an active application for this program'
                ], 409);
            }

            $application = InternshipApplication::create([
                'programs_id' => $programId,
                'users_id' => $userId,
                'status' => 'submitted',
                'submitted_at' => now(),
                'is_final' => 'false'
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

            $now = now();

            match ($status) {
                'verified' => $application->update([
                    'status' => 'verified',
                    'verified_at' => $now
                ]),

                'accepted' => $application->update([
                    'status' => 'accepted',
                    'is_final' => true,
                    'verified_at' => $now
                ]),

                'rejected' => $application->update([
                    'status' => 'rejected',
                    'is_final' => true,
                    'verified_at' => $now
                ])
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