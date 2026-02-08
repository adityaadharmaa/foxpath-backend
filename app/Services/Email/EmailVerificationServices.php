<?php

namespace App\Services\Email;

use App\Models\User;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailVerificationServices
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun tidak ditemukan. Silakan lakukan pendaftaran ulang.'
            ], 404);
        }

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
                'status' => 'success',
                'message' => 'Email already verified.',
            ], 200);
        }

        $user->sendVerificationEmail($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Verification link resent. Please check your email.',
        ], 200);
    }

    public function resendPublic(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'success',
                'message' => 'Jika email terdaftar, link verifikasi telah dikirim.'
            ], 200);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email sudah terverifikasi sebelumnya. Silakan login.',
            ], 200);
        }

        $this->sendVerificationEmail($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Link verifikasi baru telah dikirim ke email Anda.',
        ], 200);
    }

    public function sendVerificationEmail(User $user): void
    {
        $user->notify(new VerifyEmailQueued);
    }
}
