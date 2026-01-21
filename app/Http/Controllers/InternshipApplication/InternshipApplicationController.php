<?php

namespace App\Http\Controllers\InternshipApplication;

use App\Http\Controllers\Controller;
use App\Http\Requests\InternshipApplications\StoreInternshipApplicationRequest;
use App\Http\Requests\InternshipApplications\UpdateInternshipApplicationRequest;
use App\Services\InternshipApplication\InternshipApplicationService;
use Illuminate\Http\Request;

class InternshipApplicationController extends Controller
{
    public function __construct(
        protected InternshipApplicationService $applicationService
    )
    {}

    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $filters = $request->only(['page', 'per_page', 'q', 'status']);

        return $this->applicationService->index($user->id, $isAdmin, $filters);
    }

    public function show(int $id)
    {
        $user = auth()->user();

        $isAdmin = $user->hasRole('admin');
        return $this->applicationService->show($id, $user->id, $isAdmin);
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
