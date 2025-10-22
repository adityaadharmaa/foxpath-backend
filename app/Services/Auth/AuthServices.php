<?php 

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Request;

class AuthService 
{
  public function login(Request $r)
  {
    $validate = $r->validate([
      'username' => 'required|string|unique:users,username|max:255',
      'password' => 'required|min:8'
    ],[
      'username.required' => 'Username is required',
      'password.required' => 'Password is required'
    ]);

    return $validate;
  }
}