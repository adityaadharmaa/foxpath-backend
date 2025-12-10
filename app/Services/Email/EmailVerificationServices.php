<?php

namespace App\Services\Email;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailVerificationServices
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        DB::beginTransaction();

        try {
            if ($user->is_active === 1) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Email already verified.',
                ], 200);
            } else {
                $user->markEmailAsVerified();

                $user->is_active = 1;
                $user->save();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Email successfully verified.',
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify email.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified.',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => 'success',
            'message' => 'Verification email resent.',
        ], 200);
    }

    public function sendVerificationEmail(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }
}
