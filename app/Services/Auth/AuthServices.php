<?php 

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthServices 
{
  public function login(Request $request)
  {
    $request->validate([
      'login' => 'required|string',
      'password' => 'required|min:8'
    ],[
      'login.required' => 'Email or Username is required',
      'password.required' => 'Password is required'
    ]);

   $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL)
    ? 'email'
    : 'username';

    $user = User::select('users.id', 'users.username', 'users.email', 'users.password', 'users.roles_id', 'roles.name as roles_name')
    ->join('roles', 'users.roles_id', '=', 'roles.id')
    ->leftJoin('profiles', 'users.id', '=', 'profiles.users_id')
    ->where($loginType, $request->login)
    ->first();

    // dd($user);

    if(!$user || !Hash::check($request->password, $user->password)){
      return response()->json([
        'message' => 'Invalid Credentials'
      ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    $requestedirectTo = match($user->roles_name){
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
        'redirect_to' => $requestedirectTo,
      ]
    ], 200);

  }

  public function register(Request $request){
    // Register Logic Here
    $data = $request->validate([
      'username' => 'required|string|unique:users,username',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:8|confirmed'
    ],[
      'username.required' => 'Username is required',
      'username.unique' => 'Username already exists',
      'email.required' => 'Email is required',
      'email.email' => 'Email is not valid',
      'email.unique' => 'Email already exists',
      'password.required' => 'Password is required',
      'password.confirmed' => 'Password confirmation does not match',
    ]);

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

    return response()->json([
      'status' => 'success',
      'message' => 'Registration Successfully',
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
}