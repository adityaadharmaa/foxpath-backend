<?php

namespace App\Services\Auth;

use App\Mail\ResetPasswordMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{  
   public function sendResetLink(string $email)
   {
        $email = trim(strtolower($email));

        Log::info('DEBUG RESET PASSWORD', [
            'input_email' => $email,
            'user_exists' => User::where('email', $email)->exists()
        ]);

        DB::beginTransaction();
        // dd([
        //     'db' => DB::connection()->getDatabaseName(),
        //     'users_count' => \App\Models\User::count(),
        //     'email' => $email,
        // ]);

        try {

            $user = User::where('email', $email)->first();

            // Anti email enumeration
            if (!$user) {
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'If the email is registered, a password reset link has been sent.',
                ], 200);
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
                . "/reset-password?token={$token}&email={$email}";

            Mail::to($user->email)->queue(
                (new ResetPasswordMail($user, $resetUrl))->onQueue('emails')
            );

            $user->notify(new ResetPasswordNotification());

            return response()->json([
                'status' => 'success',
                'message' => 'If the email is registered, a password reset link has been sent.',
            ], 200);

        } catch (\Throwable $e) {
            if(DB::transactionLevel() > 0)
            {
                DB::rollBack();
            }

            Log::error('SEND RESET LINK ERROR', [
                'email' => $email,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Penting untuk debugging
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Failed to process forgot password request.',
            ], 500);
        }
    }

    public function resetPassword(array $data)
    {
        DB::beginTransaction();

        try {
            $record = PasswordResetToken::where('email', $data['email'])->first();

            if(!$record || !Hash::check($data['token'], $record->token)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or incorrect token.'
                ], 400);
            }

            if(now()->diffInMinutes($record->created_at) > 60){
                $record->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Reset token has expired. Please request a new one.'
                ], 400);
            }

            $user = User::where('email', $data['email'])->first();

            if(!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.'
                ], 404);
            }

            $user->forceFill([
                'password' => Hash::make($data['password'])
            ])->save();

            $record->delete();

            try{
                $user->notify(new PasswordChangedNotification());
            } catch (\Exception $e)
            {
                Log::error('Gagal mengirim notifikasi password changed: ', $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Password has been successfully reset.'
            ], 200);

        } catch (\Exception $e){
            DB::rollBack();

            Log::error('RESET PASSWORD ERROR', [
                'email' => $data['email'],
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reset password.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.'
            ], 500);
        }
    }
}