<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Models\APIRequests;
use App\Models\ShortURL\ShortURL;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;

/**
 * Class AdminController
 *
 * @package App\Http\Controllers\Auth
 */
class AdminController extends Controller
{
    /**
     * Returns the Dashboard View
     *
     * @return View
     */
    public function showDashboardView() : View
    {
        Log::debug('Admin Dashboard View requested');

        return view('admin.dashboard')
            ->with('users', User::all())
            ->with('users_today', count(User::whereDate('created_at', '=', Carbon::today()->toDateString())->get()))
            ->with('urls', ShortURL::all())
            ->with('urls_today', count(ShortURL::whereDate('created_at', '=', Carbon::today()->toDateString())->get()))
            ->with('api_requests', count(APIRequests::whereDate('created_at', '=', Carbon::today()->toDateString())->get()))
            ->with('logins', count(User::whereDate('last_login', '=', Carbon::today()->toDateString())->get()))
            ->with('logs', LaravelLogViewer::all())
            ->with('files', LaravelLogViewer::getFiles(true))
            ->with('current_file', LaravelLogViewer::getFileName());
    }

    /**
     * @return View
     */
    public function showLogsView() : View
    {
        Log::debug('Admin Logs View requested');

        return view('admin.logs')
                    ->with('logs', LaravelLogViewer::all())
                    ->with('files', LaravelLogViewer::getFiles(true))
                    ->with('current_file', LaravelLogViewer::getFileName());
    }

    /**
     * Returns the View to list all routes
     *
     * @return View
     */
    public function showRoutesView() : View
    {
        Log::debug('Admin Routes View requested');

        return view('admin.routes.index');
    }
}
