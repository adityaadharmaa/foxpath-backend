<?php

namespace App\Http\Controllers\Criteria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Criterias\CriteriaExportRequest;
use App\Http\Requests\Criterias\CriteriaIndexRequest;
use App\Http\Requests\Criterias\StoreCriteriaRequest;
use App\Http\Requests\Criterias\UpdateCriteriaRequest;
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

    public function update(UpdateCriteriaRequest $request, $id)
    {
        return $this->criteriaService->update($request->validated(), (int) $id);
    }

    public function destroy($id)
    {
        return $this->criteriaService->destroy((int) $id);
    }

    public function restore($id)
    {
        return $this->criteriaService->restore((int) $id);
    }

    public function toggle($id)
    {
        return $this->criteriaService->toggle((int) $id);
    }

    public function export(CriteriaExportRequest $request)
    {
        return $this->criteriaService->export($request->validated());
    }
}
