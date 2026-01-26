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
}
