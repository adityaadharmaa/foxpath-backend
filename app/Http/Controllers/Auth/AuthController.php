<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginReguest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthServices;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public $authService;
    function __construct(AuthServices $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(ForgotPasswordRequest $request)
    {
        return $this->authService->sendResetLink($request->email);
    }

    public function login(LoginReguest $request)
    {
        return $this->authService->login($request);
    }

    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request);
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }
}
