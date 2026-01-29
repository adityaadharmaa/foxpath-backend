<?php

namespace App\Http\Controllers\Applicants;

use App\Http\Controllers\Controller;
use App\Http\Requests\Placement\UpdatePlacementRequest;
use App\Services\ApplicationPlacement\ApplicationPlacementService;

class ApplicationPlacementController extends Controller
{
    public function __construct(
        protected ApplicationPlacementService $placement
    ) {}

    public function update(UpdatePlacementRequest $request, int $id)
    {
        return $this->placement->overrideDates(
            $id,
            $request->start_date,
            $request->end_date,
            $request->duration_months,
        );
    }
}
