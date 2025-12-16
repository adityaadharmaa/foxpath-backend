<?php

namespace App\Http\Controllers\Program;

use App\Http\Controllers\Controller;
use App\Http\Requests\Programs\StoreProgramRequest;
use App\Services\Program\ProgramService;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function __construct(
        protected ProgramService $programService
    ) {}

    public function index(Request $request)
    {
        return $this->programService->index($request);
    }

    public function store(StoreProgramRequest $request)
    {
        return $this->programService->store($request);
    }

    public function destroy(int $id)
    {
        return $this->programService->destroy($id);
    }
}
