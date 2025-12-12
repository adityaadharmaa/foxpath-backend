<?php

namespace App\Http\Controllers\Program;

use App\Http\Controllers\Controller;
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

    public function adminIndex(Request $request)
    {
        return $this->programService->adminIndex($request);
    }
}
