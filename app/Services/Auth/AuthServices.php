<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginReguest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\NewUserRegisterNotification;
use App\Notifications\ResetPasswordQueued;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class AuthServices
{
    public $emailService;

    public function __construct(EmailVerificationServices $emailService)
    {
        $this->emailService = $emailService;
    }

    public function login(LoginReguest $request)
    {
        try {
            $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL)
                  ? 'email'
                  : 'username';

            $user = User::select(
                'users.id',
                'users.username',
                'users.email',
                'users.password',
                'users.roles_id',
                'users.is_active',
                'roles.name as roles_name'
            )
                ->join('roles', 'users.roles_id', '=', 'roles.id')
                ->leftJoin('profiles', 'users.id', '=', 'profiles.users_id')
                ->where("users.$loginType", $request->login)
                ->first();

            // dd($user);

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Credentials',
                ], 401);
            }

            if ($user->is_active === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is not active. Please check your email to activate your account.',
                ], 403);
            }

            $remember = $request->boolean('remember_me');

            $expiresAt = $remember ? now()->addYear() : now()->addHours(4);

            $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

            $redirectTo = match ($user->roles_name) {
                'admin' => '/admin/dashboard',
                'users' => '/',
                default => '/login',
            };

            logger()->info('USER MELAKUKAN LOGIN:  ', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->roles_name
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login Successful',
                'data' => [
                    'user' => [
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->roles_name,
                    ],
                ],
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => $remember ? $expiresAt->format('Y-m-d') : $expiresAt->format('Y-m-d H:i:s'),
                ],
                'meta' => [
                    'redirect_to' => $redirectTo,
                ],
            ], 200);
        } catch (\Exception $e) {
            logger()->error('USER GAGAL LOGIN : ', [
                'user_id' => $user->id,
                'uername' => $user->username,
                'email' => $user->email,
                'role' => $user->roles_name
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to login.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ], 500);
        }

    }

    public function register(RegisterRequest $request)
    {
        // Register Logic Here
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'roles_id' => 2,
                'is_active' => 0,
            ]);

            $user->profile()->create();

            DB::commit();

            $this->emailService->sendVerificationEmail($user);

            $admins = User::where('roles_id', 1)->get();

            if ($admins->count() > 0) {
                try {
                    Notification::send($admins, new NewUserRegisterNotification($user));
                } catch (\Exception $e) {
                    logger()->error('Gagal mengirim notifikasi admin: '.$e->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful. Please check your email to verify your account.',
                'data' => [
                    'user' => [
                        'username' => $user->username,
                        'email' => $user->email,
                        'profile_completed' => false,
                    ],
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Registration Failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            logger()->info('USER LOGOUT : ', [
              'users_id' => auth()->id(),
              'username' => auth()->username,
              'email' => auth()->email,
              'role' => auth()->roles_name,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Logout Successful',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout Failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendResetLink(string $email)
    {
        DB::beginTransaction();

        try {
            $user = User::where('email', $email)->first();

            // Anti email enumeration
            if (! $user) {
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'If the email is registered, a password reset link has been sent',
                ]);
            }

            PasswordResetToken::where('email', $email)->delete();

            $token = Str::random(64);

            PasswordResetToken::create([
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            DB::commit();

            $resetUrl = config('app.frontend_url')
                ."/reset-password?token={$token}&email={$email}";

            $user->refresh()->notify(
                new ResetPasswordQueued($resetUrl)
            );

            return response()->json([
                'status' => 'success',
                'message' => 'If the email is registered, a password reset link has been sent',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error('FORGOT PASSWORD ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process forgot password request.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ], 500);
        }
    }
}
