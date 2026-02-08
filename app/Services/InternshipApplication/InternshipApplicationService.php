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
        $profile = Profile::where('users_id', $userId)->with('activeEducation')->first();

        if (!$profile) {
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

        foreach ($requiredFields as $field) {
            if (!isset($profile->$field) || trim((string)$profile->$field) === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your profile is incomplete.',
                    'code' => 'PROFILE_INCOMPLETE',
                    'missing_field' => $field
                ], 422);
            }
        }

        $education = $profile->activeEducation;

        if (!$education) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please complete your education data.'
            ], 422);
        }

        if ($profile->applicant_type === 'mahasiswa' && empty($education->gpa)) {
            return response()->json([
                'status' => 'error',
                'message' => 'GPA (IPK) is required for university students.'
            ], 422);
        }

        if ($profile->applicant_type === 'siswa' && empty($education->average_score)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Average report score is required for students.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $program = Program::where('id', $programId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$program) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Program not available'
                ], 404);
            }

            $activeExists = InternshipApplication::where('programs_id', $programId)
                ->where('users_id', $userId)
                ->where('is_final', false)
                ->exists();

            if ($activeExists) {
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
                'is_final' => false
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Application submitted successfully',
                'data' => [
                    'application' => $application
                ]
            ], 201);
        } catch (\Exception $e) {
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
        $application = InternshipApplication::lockForUpdate()->find($applicationId);

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

    public function index(int $userId, bool $isAdmin = false, array $filters = [])
    {
        // DB::beginTransaction();

        try {
            $query = InternshipApplication::with([
                'program:id,name',
                'user.profile'
            ])
                ->latest();

            if (!$isAdmin) {
                $query->where('users_id', $userId);
            }

            if (isset($filters['q']) && $filters['q']) {
                $q = $filters['q'];
                $query->whereHas('user.profile', function ($sub) use ($q) {
                    $sub->where('full_name', 'like', "%${q}%");
                })->orWhereHas('program', function ($sub) use ($q) {
                    $sub->where('name', 'like', "%${q}");
                });
            }

            if (isset($filters['status']) && $filters['status']) {
                $query->where('status', $filters['status']);
            };

            $perPage = $filters['per_page'] ?? 10;
            $applications = $query->paginate($perPage);

            // DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Application history retrieved successfully.',
                'data' => $applications->items(),
                'meta' => [
                    'pagination' => [
                        'current_page' => $applications->currentPage(),
                        'per_page' => $applications->perPage(),
                        'total' => $applications->total(),
                        'last_page' => $applications->lastPage(),
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {

            // DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve application history.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(int $applicationId, int $userId, bool $isAdmin = false)
    {
        // DB::beginTransaction();

        try {
            $query = InternshipApplication::with([
                'program',
                'documents',
                'user.profile',
                'user.profileEducation',
                'scores.criteria'
            ])
                ->where('id', $applicationId);

            if (!$isAdmin) {
                $query->where('users_id', $userId);
            }

            $application = $query->first();

            if (!$application) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Application not found.'
                ], 404);
            }

            // DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Application detail retrieved successfully.',
                'data' => $application
            ], 200);
        } catch (\Exception $e) {

            // DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve application detail.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
