<?php 

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginReguest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Email\EmailVerificationServices;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthServices 
{
  public $emailService;
  public function __construct(EmailVerificationServices $emailService)
  {
    $this->emailService = $emailService;
  }
  public function login(LoginReguest $request)
  {
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
      'roles.name as roles_name')
    ->join('roles', 'users.roles_id', '=', 'roles.id')
    ->leftJoin('profiles', 'users.id', '=', 'profiles.users_id')
    ->where("users.$loginType", $request->login)
    ->first();

    // dd($user);

    if(!$user || !Hash::check($request->password, $user->password)){
      return response()->json([
        'status' => 'error',
        'message' => 'Invalid Credentials'
      ], 401);
    }

    if($user->is_active === 0){
      return response()->json([
        'status' => 'error',
        'message' => 'Your account is not active. Please check your email to activate your account.'
      ], 403);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    $redirectTo = match($user->roles_name){
      'admin' => '/admin',
      'user'  => '/dashboard',
      default => '/login',
    };

    return response()->json([
      'status' => 'success',
      'message' => 'Login Successful',
      'data' => [
        'user' => [
          'username' => $user->username,
          'email' => $user->email,
          'role' => $user->roles_name,
        ]
      ],
      'token' => [
        'access_token' => $token,
        'token_type' => 'Bearer'
      ],
      'meta' => [
        'redirect_to' => $redirectTo,
      ]
    ], 200);

  }

  public function register(RegisterRequest $request){
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

    return response()->json([
      'status' => 'success',
      'message' => 'Registration successful. Please check your email to verify your account.',
      'data' => [
        'user' => [
          'username' => $user->username,
          'email' => $user->email,
          'profile_completed' => false,
        ]
      ]
    ], 201);

    } catch (\Throwable $e){
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
    try{
      $request->user()->currentAccessToken()->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'Logout Successful',
      ], 200);
    } catch(\Exception $e){
      return response()->json([
        'status' => 'error',
        'message' => 'Logout Failed',
        'error' => $e->getMessage(),
      ], 500);
    }

  }
}