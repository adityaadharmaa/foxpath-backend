<?php 

namespace App\Services\ApplicationDocument;

use App\Models\InternshipApplication;

class ApplicationDocumentStatusService
{
    public function checkCompleteness(InternshipApplication $application)
    {
        $requiredTypes = [
            'cv',
            'transcript',
            'portofolio',
        ];

        $uploadedTypes = $application->documents
        ->pluck('type')
        ->unique()
        ->toArray();

        $documentStatus = [];

        foreach ($requiredTypes as $type) {
            $documentStatus[$type] = in_array($type, $uploadedTypes);
        }

        $isComplete = !in_array(false, $documentStatus, true);

        return [
            'application_id' => $application->id,
            'documents' => $documentStatus,
            'is_complete' => $isComplete,
        ];
    }
}