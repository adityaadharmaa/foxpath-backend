<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if(!$user){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized Access',
            ], 401);
        }

        $roleName = $user->roles_name ?? $user->roles->name ?? null;
        if($roleName === 'admin' || $user->roles_id = 1){
            return $next($request);
        }

        $profile = $user->profile;
        $education = $profile?->educations()?->where('is_current', 1)->first();

        $isComplete = $profile
            && !empty($profile->applicant_type)
            && !empty($profile->student_identifier)
            && !empty($profile->full_name)
            && !empty($profile->phone)
            && !empty($profile->address)
            && !empty($profile->date_of_birth)
            && $education
            && !empty($education->institution_name_raw)
            && !empty($education->level)
            && !empty($education->program);

        if(!$isComplete){
            return response()->json([
                'status' => 'error',
                'message' => 'Lengkapi profil Anda sebelum mendaftar magang.',
                'profile_completed' => false,
            ], 403);
        }

        return $next($request);
    }
}