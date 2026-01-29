<?php

namespace App\Services\ApplicationScore;

use App\Models\{
    InternshipApplication,
    ApplicationScore,
    Criteria
};
use Illuminate\Support\Facades\Log;

class AcademicScoreService
{
    public function sync(InternshipApplication $application)
    {
        $application->loadMissing('user.profile', 'user.profileEducation');

        $profile = $application->user->profile;
        $education = $application->user->profileEducation;

        if (!$profile || !$education) {
            Log::warning('Academic sync skipped: profile or education missing', [
                'application_id' => $application->id
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed. User profile or education data is missing.'
            ], 422);
        }

        $criteria = Criteria::where('code', 'C1')
            ->where('is_active', true)
            ->first();

        if (!$criteria) {
            Log::warning('Academic sync skipped: criteria not found');
            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed. Criteria code "C1" (Academic) not found or inactive.'
            ], 500);
        }

        $finalValue = 0;
        $originalValue = 0;

        if ($profile->applicant_type === 'mahasiswa') {
            $originalValue = $education->gpa ?? 0;

            if ($originalValue > 0) {
                $finalValue = ($originalValue / 4.00) * 100;
            }
        } else {
            $originalValue = $education->average_score ?? 0;
            $finalValue = $originalValue;
        }

        if ($finalValue <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sync skipped. Academic score is 0 or invalid.',
                'debug' => [
                    'type' => $profile->applicant_type,
                    'original' => $originalValue
                ]
            ], 422);
        }

        try {
            $score = ApplicationScore::updateOrCreate(
                [
                    'internship_applications_id' => $application->id,
                    'criterias_id' => $criteria->id
                ],
                [
                    'value' => round($finalValue, 2)
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Academic score synced successfully.',
                'data' => [
                    'applicant_id' => $application->id,
                    'applicant_type' => $profile->applicant_type,
                    'original_score' => $originalValue,
                    'converted_score' => round($finalValue, 2),
                    'score_id' => $score->id
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Academic sync error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Database error during synchronization.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
