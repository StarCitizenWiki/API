<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequests;
use App\Models\Notification;
use App\Models\ShortUrl\ShortUrl;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Jackiedo\LogReader\Facades\LogReader;

/**
 * Class AdminController
 *
 * @package App\Http\Controllers\Admin
 */
class AdminController extends Controller
{
    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:admin');
    }

    /**
     * Returns the Dashboard View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showDashboardView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        $today = Carbon::today()->toDateString();

        $logs = $this->getLogs();

        $users = [
            'overall'       => User::all()->count(),
            'last'          => User::take(5)->orderBy('created_at', 'desc')->get(),
            'registrations' => [
                'counts' => [
                    'last_hour' => User::whereDate('created_at', '>', Carbon::now()->subHour())->count(),
                    'today'     => User::whereDate('created_at', '=', $today)->get()->count(),
                    'overall'   => User::all()->count(),
                ],
            ],
            'logins'        => [
                'counts' => [
                    'last_hour' => User::whereDate('last_login', '>', Carbon::now()->subHour())->count(),
                    'today'     => User::whereDate('last_login', '=', $today)->get()->count(),
                    'overall'   => User::all()->count(),
                ],
            ],
        ];

        $apiRequests = [
            'last'   => ApiRequests::with('user')->take(5)->orderBy('created_at', 'desc')->get(),
            'counts' => [
                'last_hour' => ApiRequests::whereDate('created_at', '>', Carbon::now()->subHour())->count(),
                'today'     => ApiRequests::whereDate('created_at', '=', $today)->get()->count(),
                'overall'   => ApiRequests::all()->count(),
            ],
        ];

        $shortUrls = [
            'last'   => ShortUrl::with('user')->take(5)->orderBy('created_at', 'desc')->get(),
            'counts' => [
                'last_hour' => ShortUrl::whereDate('created_at', '>', Carbon::now()->subHour())->count(),
                'today'     => ShortUrl::whereDate('created_at', '=', $today)->get()->count(),
                'overall'   => ShortUrl::all()->count(),
            ],
        ];

        $notifications = [
            'last' => Notification::take(7)->orderByDesc('published_at')->get(),
        ];

        return view('admin.dashboard')->with(
            'users',
            $users
        )->with(
            'api_requests',
            $apiRequests
        )->with(
            'short_urls',
            $shortUrls
        )->with(
            'notifications',
            $notifications
        )->with(
            'logs',
            $logs
        );
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLogsView(Request $request)
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

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
     * @return \Illuminate\Contracts\View\View
     */
    public function showRoutesView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.routes.index');
    }

    /**
     * @return array
     */
    private function getLogs(): array
    {
        $lastHour = Carbon::now()->subHour();
        $today = Carbon::today();

        $filterLastHour = function ($item) use ($lastHour) {
            return $lastHour->lessThanOrEqualTo(Carbon::parse($item->date));
        };

        $filterLastDay = function ($item) use ($today) {
            return $today->lessThanOrEqualTo(Carbon::parse($item->date));
        };

        $debug = LogReader::level('debug')->get();
        $info = LogReader::level('info')->get();
        $notice = LogReader::level('notice')->get();
        $warning = LogReader::level('warning')->get();
        $error = LogReader::level('error')->get();
        $critical = LogReader::level('critical')->get();
        $danger = LogReader::level('danger')->get();
        $emergency = LogReader::level('emergency')->get();

        $logs = [
            'debug'     => [
                'last_hour' => $debug->filter($filterLastHour),
                'today'     => $debug->filter($filterLastDay),
                'all'       => $debug,
            ],
            'info'      => [
                'last_hour' => $info->filter($filterLastHour),
                'today'     => $info->filter($filterLastDay),
                'all'       => $info,
            ],
            'notice'    => [
                'last_hour' => $notice->filter($filterLastHour),
                'today'     => $notice->filter($filterLastDay),
                'all'       => $notice,
            ],
            'warning'   => [
                'last_hour' => $warning->filter($filterLastHour),
                'today'     => $warning->filter($filterLastDay),
                'all'       => $warning,
            ],
            'error'     => [
                'last_hour' => $error->filter($filterLastHour),
                'today'     => $error->filter($filterLastDay),
                'all'       => $error,
            ],
            'critical'  => [
                'last_hour' => $critical->filter($filterLastHour),
                'today'     => $critical->filter($filterLastDay),
                'all'       => $critical,
            ],
            'danger'    => [
                'last_hour' => $danger->filter($filterLastHour),
                'today'     => $danger->filter($filterLastDay),
                'all'       => $danger,
            ],
            'emergency' => [
                'last_hour' => $emergency->filter($filterLastHour),
                'today'     => $emergency->filter($filterLastDay),
                'all'       => $emergency,
            ],
        ];

        return $logs;
    }
}
