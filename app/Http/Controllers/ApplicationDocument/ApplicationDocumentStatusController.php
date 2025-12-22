<?php

namespace App\Http\Controllers\ApplicationDocument;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InternshipApplication;
use App\Services\ApplicationDocument\ApplicationDocumentStatusService;

class ApplicationDocumentStatusController extends Controller
{
    public function __construct(
        protected ApplicationDocumentStatusService $documentStatusService
    )
    {}

    public function show(InternshipApplication $application)
    {
        if($application->users_id !== auth()->id()){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized application access.'
            ], 403);
        }

        $result = $this->documentStatusService->checkCompleteness($application);

        return response()->json([
            'status' => 'success',
            'message' => 'Document status retrieved successfully.',
            'data' => $result
        ]);
    }
}
