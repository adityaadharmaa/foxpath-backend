<?php

namespace App\Http\Controllers\Program;

use App\Http\Controllers\Controller;
use App\Http\Requests\Programs\ProgramExportRequest;
use App\Http\Requests\Programs\ProgramIndexRequest;
use App\Http\Requests\Programs\StoreProgramRequest;
use App\Http\Requests\Programs\UpdateProgramRequest;
use App\Services\Program\ProgramService;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function __construct(
        protected ProgramService $programService
    ) {}

    public function index(ProgramIndexRequest $request)
    {
        return $this->programService->index($request);
    }

    public function applicants(Request $request, int $programId)
    {
        return $this->programService->getApplicantsByProgram($programId, $request->query('status'), $request->query('result'));
    }

    public function store(StoreProgramRequest $request)
    {
        return $this->programService->store($request);
    }

    public function destroy(int $id)
    {
        return $this->programService->destroy($id);
    }

    public function activate(int $id)
    {
        return $this->programService->activate($id);
    }

    public function restore(int $id)
    {
        return $this->programService->restore($id);
    }

    public function update(UpdateProgramRequest $request, int $id) {
        return $this->programService->update($request ,$id);
    }

    public function export(ProgramExportRequest $request)
    {
        return $this->programService->export($request->validated());
    }

    public function summary()
    {
        return $this->programService->summary();
    }
}
