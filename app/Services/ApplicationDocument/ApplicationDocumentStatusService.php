<?php 

namespace App\Services\ApplicationDocument;

use App\Models\InternshipApplication;

class ApplicationDocumentStatusService
{
    public function checkCompleteness(int $applicationId, int $userId)
    {
        $application = InternshipApplication::with('documents')
        ->find($applicationId);

        if(!$application)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Application not found.'
            ], 404);
        }

        if($application->users_id !== $userId)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized application access.'
            ], 403);
        }

        $requiredTypes = [
            'cv',
            'transcript',
        ];

        $uploadedTypes = $application->documents
        ->pluck('type')
        ->unique()
        ->toArray();

        $documentStatus = [];

        foreach ($requiredTypes as $type) {
            $documentStatus[$type] = in_array($type, $uploadedTypes, true);
        }

        $isComplete = !in_array(false, $documentStatus, true);

        return response()->json([
            'status' => 'success',
            'message' => 'Document status recieved successfully.',
            'data' => [
                'application_id' => $application->id,
                'status' => $application->status,
                'documents' => $documentStatus,
                'is_complete' => $isComplete,
            ]
        ], 200);
    }
}