<?php 

namespace App\Services\ApplicationScore;

use App\Models\{
    InternshipApplication,
    ApplicationScore,
    Criteria
};

class AcademicScoreService
{
    public function sync(InternshipApplication $application)
    {
        $application->loadMissing('user.profile', 'user.profileEducation');

        $profile = $application->user->profile;
        $education = $application->user->profileEducation;

        if(!$profile || !$education)
        {
            logger()->warning('Academic sync skipped: profile or education missing', [
            'application_id' => $application->id
            ]);
            return;
        }

        $criteria = Criteria::where('code', 'C1')
        ->where('is_active', true)
        ->first();

        if(!$criteria)
        {
            logger()->warning('Academic sync skipped: criteria not found');
            return;
        }

        $value = $profile->applicant_type === 'mahasiswa'
        ? $education->gpa
        : $education->average_score;

        if($value === null)
        {
            return;
        }

        ApplicationScore::updateOrCreate(
            [
                'internship_applications_id' => $application->id,
                'criterias_id' => $criteria->id
            ],
            [
                'value' => $value
            ]
        );
    }
}