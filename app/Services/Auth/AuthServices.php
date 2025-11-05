<?php 

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

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
}