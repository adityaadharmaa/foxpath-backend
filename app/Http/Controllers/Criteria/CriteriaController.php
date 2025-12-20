<?php

namespace App\Http\Controllers\Criteria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Criterias\CriteriaIndexRequest;
use App\Http\Requests\Criterias\StoreCriteriaRequest;
use App\Services\Criteria\CriteriaService;
use Symfony\Component\HttpKernel\HttpCache\Store;

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

    public function store(StoreCriteriaRequest $request)
    {
        return $this->criteriaService->store($request->validated());
    }
}
