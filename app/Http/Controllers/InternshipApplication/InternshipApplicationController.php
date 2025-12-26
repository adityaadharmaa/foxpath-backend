<?php

namespace App\Http\Controllers\InternshipApplication;

use App\Http\Controllers\Controller;
use App\Http\Requests\InternshipApplications\StoreInternshipApplicationRequest;
use App\Http\Requests\InternshipApplications\UpdateInternshipApplicationRequest;
use App\Services\InternshipApplication\InternshipApplicationService;

class InternshipApplicationController extends Controller
{
    public function __construct(
        protected InternshipApplicationService $applicationService
    )
    {}

    public function index()
    {
        $userId = auth()->id();
        return $this->applicationService->index($userId);
    }

    public function show(int $id)
    {
        $userId = auth()->id();
        return $this->applicationService->show($id, $userId);
    }

    public function store(StoreInternshipApplicationRequest $request)
    {
        return $this->applicationService->store(
            $request->user()->id,
            $request->validated()['programs_id']
        );
    }

    public function updateStatus(
        UpdateInternshipApplicationRequest $request, 
        int $id
    ) {
        return $this->applicationService->updateStatus(
            (int) $id,
            $request->validated()['status'],
        );
    }
}
