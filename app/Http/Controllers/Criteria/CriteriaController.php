<?php

namespace App\Http\Controllers\Criteria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Criterias\CriteriaIndexRequest;
use App\Services\Criteria\CriteriaService;

class CriteriaController extends Controller
{
    public function __construct(
        protected CriteriaService $criteriaService
    )
    {}

    public function index(CriteriaIndexRequest $request)
    {
        return $this->criteriaService->index($request->validated());
    }
}
