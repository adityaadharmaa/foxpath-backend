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

    public function show(int $applicationId)
    {
        return $this->documentStatusService->checkCompleteness(
            $applicationId,
            auth()->id()
        );
    }
}
