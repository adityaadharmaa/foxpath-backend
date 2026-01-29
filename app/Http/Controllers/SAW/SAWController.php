<?php

namespace App\Http\Controllers\SAW;

use App\Http\Controllers\Controller;
use App\Services\SAW\SawCalculationService;
use Illuminate\Http\Request;

class SAWController extends Controller
{
    public function __construct(
        protected SawCalculationService $service
    ) {}

    public function calculate($programId)
    {
        return $this->service->calculateByProgram((int)$programId);
    }

    public function details($programId)
    {
        return $this->service->getCalculationDetails($programId);
    }
}
