<?php

namespace App\Http\Controllers\Applicants;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationScore\StoreApplicationScoreRequest;
use App\Services\ApplicationScore\ApplicationScoreService;
use Illuminate\Http\Request;

class ApplicationScoreController extends Controller
{
    public function __construct(
        protected ApplicationScoreService $service
    )
    {}

    public function store(StoreApplicationScoreRequest $request, int $applicationId)
    {
        return $this->service->store(
            $applicationId, 
            $request->validated()
        );
    }
}
