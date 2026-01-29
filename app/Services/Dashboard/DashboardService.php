<?php

namespace App\Services\Dashboard;

use App\Models\InternshipApplication;
use App\Models\Program;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function analytics()
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(29);

        $trends = InternshipApplication::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $deadlines = Program::where('is_active', 1)
            ->whereNotNull('registration_ends_at')
            ->where('registration_ends_at', '>=', now())
            ->where('registration_ends_at', '<=', now()->addDays(7))
            ->orderBy('registration_ends_at', 'asc')
            ->take(5)
            ->get(['id', 'name', 'registration_ends_at']);

        $recent_activities = InternshipApplication::with([
            'user.profile:users_id,full_name,profile_picture',
            'program:id,name'
        ])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'trends' => $trends,
                'deadlines' => $deadlines,
                'recent_activities' => $recent_activities->map(function ($app) {
                    return [
                        'id' => $app->id,
                        'user_name' => $app->user->profile->full_name ?? $app->user->email,
                        'program_name' => $app->program->name,
                        'status' => $app->status,
                        'created_at' => $app->created_at
                    ];
                })
            ]
        ], 200);
    }

    public function getSchoolStats()
    {
        $stats = InternshipApplication::query()
            // 1. Hubungkan Application ke User
            ->join('users', 'internship_applications.users_id', '=', 'users.id')
            // 2. Hubungkan User ke Profile (Karena pendidikan ada di bawah profile)
            ->join('profiles', 'users.id', '=', 'profiles.users_id')
            // 3. Hubungkan Profile ke Profile Education (Sesuai screenshot: profiles_id)
            ->join('profile_education', 'profiles.id', '=', 'profile_education.profiles_id')
            ->select(
                'profile_education.institution_name as name',
                DB::raw('count(*) as value')
            )
            ->groupBy('profile_education.institution_name')
            ->orderByDesc('value')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ], 200);
    }
}
