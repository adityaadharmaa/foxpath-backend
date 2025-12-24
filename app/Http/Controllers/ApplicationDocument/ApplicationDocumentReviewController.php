<?php

namespace App\Http\Controllers\ApplicationDocument;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationDocument\ReviewApplicationDocumentRequest;
use App\Models\ApplicationDocument;
use App\Services\ApplicationDocument\ApplicationDocumentReviewService;
use Illuminate\Http\Request;

class ApplicationDocumentReviewController extends Controller
{
    public function __construct(
        protected ApplicationDocumentReviewService $reviewService
    )
    {}

    public function review(
        ReviewApplicationDocumentRequest $request,
        int $documentId
    )
    {
        return $this->reviewService->review(
            $documentId, 
            $request->validated()['status'],
            $request->validated()['review_note'] ?? null,
            auth()->id()
        );
    }
}
