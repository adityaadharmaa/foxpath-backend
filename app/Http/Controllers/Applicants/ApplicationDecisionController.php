<?php

namespace App\Http\Controllers\Applicants;

use App\Http\Controllers\Controller;
use App\Services\ApplicationDecision\ApplicationDecisionService;
use Illuminate\Http\Request;

class ApplicationDecisionController extends Controller
{
    public function __construct(
        protected ApplicationDecisionService $decision
    ){}

    public function decide(int $programId)
    {
        return $this->decision->decideByQuota($programId);
    }
}
