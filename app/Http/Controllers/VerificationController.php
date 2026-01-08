<?php

namespace App\Http\Controllers;

use App\Services\Email\EmailVerificationServices;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    protected $emailService;

    public function __construct(EmailVerificationServices $emailService)
    {
        $this->emailService = $emailService;
    }

    public function verify(Request $request, $id, $hash)
    {
        return $this->emailService->verify($request, $id, $hash);
    }

    public function resend(Request $request)
    {
        return $this->emailService->resend($request);
    }

    public function resendPublic(Request $request)
    {
        return $this->emailService->resendPublic($request);
    }
}
