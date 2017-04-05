<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
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
        return view('admin.dashboard')
            ->with('users', User::all())
            ->with('urls', ShortURL::all())
            ->with('api_requests', count(User::whereDate('api_token_last_used', '=', Carbon::today()->toDateString())))
            ->with('logins', count(User::whereDate('last_login', '=', Carbon::today()->toDateString())))
            ->with('logs', LaravelLogViewer::all())
            ->with('files', LaravelLogViewer::getFiles(true))
            ->with('current_file', LaravelLogViewer::getFileName())
            ;
    }

    /**
     * Returns the View to list all routes
     *
     * @return View
     */
    public function showRoutesView() : View
    {
        return view('admin.routes.index');
    }
}
