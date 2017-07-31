<?php declare(strict_types = 1);

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Models\APIRequests;
use App\Models\ShortURL\ShortURL;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;

/**
 * Class AdminController
 *
 * @package App\Http\Controllers\Auth
 */
class AdminController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Returns the Dashboard View
     *
     * @return View
     */
    public function showDashboardView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

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
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__));

        if ($request->input('l')) {
            $this->addTrace("Setting File to {$request->input('l')}", __FUNCTION__, __LINE__);
            LaravelLogViewer::setFile(base64_decode($request->input('l')));
        }

        if ($request->input('dl')) {
            $this->addTrace(__FUNCTION__, "Downloading {$request->input('dl')}", __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return response()->download(LaravelLogViewer::pathToLogFile(base64_decode($request->input('dl'))));
        }

        $this->stopProfiling(__FUNCTION__);

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
    public function showRoutesView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.routes.index');
    }
}
