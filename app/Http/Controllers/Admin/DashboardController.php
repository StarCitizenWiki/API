<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $queuedBreakdown = DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as count'))
            ->groupBy('queue')
            ->orderByDesc('count')
            ->orderBy('queue')
            ->get()
            ->pluck('count', 'queue')
            ->toArray();

        $failedBreakdown = DB::table('failed_jobs')
            ->select('queue', DB::raw('COUNT(*) as count'))
            ->groupBy('queue')
            ->orderByDesc('count')
            ->orderBy('queue')
            ->get()
            ->pluck('count', 'queue')
            ->toArray();

        $stats = [
            'totalUsers' => User::query()->count(),
            'totalJobs' => DB::table('jobs')->count(),
            'failedJobs' => DB::table('failed_jobs')->count(),
            'queuedBreakdown' => $queuedBreakdown,
            'failedBreakdown' => $failedBreakdown,
        ];

        return view('admin.dashboard.index', compact('stats'));
    }
}
