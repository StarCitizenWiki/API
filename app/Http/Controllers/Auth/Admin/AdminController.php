<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\APIRequests;
use App\Models\ShortURL\ShortURL;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        Log::info(get_human_readable_name_from_view_function(__FUNCTION__), Auth::user()->getBasicInfoForLog());

        $today = Carbon::today()->toDateString();

        return view('admin.dashboard')
            ->with('users', User::all())
            ->with('users_today', count(User::whereDate('created_at', '=', $today)->get()))
            ->with('urls', ShortURL::all())
            ->with('urls_today', count(ShortURL::whereDate('created_at', '=', $today)->get()))
            ->with('api_requests', count(APIRequests::whereDate('created_at', '=', $today)->get()))
            ->with('logins', count(User::whereDate('last_login', '=', $today)->get()))
            ->with('logs', LaravelLogViewer::all())
            ->with('files', LaravelLogViewer::getFiles(true))
            ->with('current_file', LaravelLogViewer::getFileName());
    }

    /**
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function showLogsView(Request $request)
    {
        Log::info(get_human_readable_name_from_view_function(__FUNCTION__), Auth::user()->getBasicInfoForLog());

        if ($request->input('l')) {
            LaravelLogViewer::setFile(base64_decode($request->input('l')));
        }

        if ($request->input('dl')) {
            return response()->download(LaravelLogViewer::pathToLogFile(base64_decode($request->input('dl'))));
        }

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
        Log::info(get_human_readable_name_from_view_function(__FUNCTION__), Auth::user()->getBasicInfoForLog());

        return view('admin.routes.index');
    }
}
