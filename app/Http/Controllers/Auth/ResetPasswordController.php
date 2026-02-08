<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Password\UpdatePasswordRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    public function __construct(
        protected PasswordResetService $service
    ) {}
    /**
     * Handle the incoming request.
     */
    public function __invoke(ResetPasswordRequest $request)
    {
        return $this->service->resetPassword(
            $request->validated()
        );
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        return $this->service->updatePassword($user, $request->validated());
    }
}
