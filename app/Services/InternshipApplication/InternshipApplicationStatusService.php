<?php 

namespace App\Services\InternshipApplication;

use App\Models\InternshipApplication;

class InternshipApplicationStatusService
{
    public function autoVerifyIfEligible(InternshipApplication $application)
    {
        if ($application->status !== 'submitted')
        {
            return;
        }

        if(!$application->hasCompleteDocuments())
        {
            return;
        }

        $application->update([
            'status' => 'verified',
            'verified_at' => now()
        ]);
    }
}