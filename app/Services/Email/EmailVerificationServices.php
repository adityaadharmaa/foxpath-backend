<?php

namespace App\Services\Email;

use App\Models\User;
use Illuminate\Http\Request;

class EmailVerificationServices 
{
  public function verify(Request $request, $id, $hash){
    $user = User::findOrFail($id);

    if(! hash_equals((string) $hash, sha1($user->getEmailForVerification()))){
      abort(403, 'Invalid verification link.');
    }

    if($user->hasVerifiedEmail()){
      return response()->json([
        'message' => 'Email already verified.'
      ], 200);
    } else {
      $user->markEmailAsVerified();

      // Activate user account upon email verification
      $user->is_active = 1;
      $user->save();

      return response()->json([
        'message' => 'Email successfully verified.'
      ], 200);
    }
  } 

  public function resend(Request $request){
    $user = $request->user();

    if($user->hasVerifiedEmail()){
      return response()->json([
        'message' => 'Email already verified.'
      ], 400);
    }

    $user->sendEmailVerificationNotification();

    return response()->json([
      'message' => 'Verification email resent.'
    ], 200);
  }

  public function sendVerificationEmail(User $user){
    $user->sendEmailVerificationNotification();
  }
}