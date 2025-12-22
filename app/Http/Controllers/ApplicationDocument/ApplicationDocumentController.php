<?php

namespace App\Http\Controllers\ApplicationDocument;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationDocument\StoreApplicationDocumentRequest;
use App\Models\InternshipApplication;
use App\Services\ApplicationDocument\ApplicationDocumentService;
use Illuminate\Http\Request;

class ApplicationDocumentController extends Controller
{
    public function __construct(
        protected ApplicationDocumentService $documentService
    )
    {}

    public function store(StoreApplicationDocumentRequest $request, InternshipApplication $application)
    {
        $file = $request->file('file');
        
       return $this->documentService->upload(
        $application,
        $request->validated()['type'],
        $request->file('file'),
        $request->user()->id
       );
    }
}
