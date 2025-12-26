<?php

namespace App\Http\Controllers\Applicants;

use App\Http\Controllers\Controller;
use App\Http\Requests\Placement\UpdatePlacementRequest;
use App\Services\ApplicationPlacement\ApplicationPlacementService;

class ApplicationPlacementController extends Controller
{
    public function __construct(
        protected ApplicationPlacementService $placement
    ){}

    public function update(UpdatePlacementRequest $request, int $id)
    {
        return $this->placement->overrideDates(
            $id,
            $request->placement_start_at,
            $request->placement_end_at,
            $request->duration_months,
        );
    }
}
