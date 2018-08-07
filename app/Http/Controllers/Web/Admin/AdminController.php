<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account\User\User;
use App\Models\Api\Notification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Jackiedo\LogReader\Facades\LogReader;

/**
 * Class AdminController
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
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function showDashboardView(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));
        $this->authorize('web.admin.dashboard.view');

        $today = Carbon::today()->toDateString();

        $users = [
            'overall' => User::all()->count(),
            'last' => User::query()->take(5)->orderBy('created_at', 'desc')->get(),
            'registrations' => [
                'counts' => [
                    'last_hour' => User::query()->whereDate('created_at', '>', Carbon::now()->subHour())->count(),
                    'today' => User::query()->whereDate('created_at', '=', $today)->get()->count(),
                    'overall' => User::all()->count(),
                ],
            ],
            'logins' => [
                'counts' => [
                    'last_hour' => User::query()->whereDate('last_login', '>', Carbon::now()->subHour())->count(),
                    'today' => User::query()->whereDate('last_login', '=', $today)->get()->count(),
                    'overall' => User::all()->count(),
                ],
            ],
        ];

        $notifications = [
            'last' => Notification::notExpired()->take(7)->orderByDesc('created_at')->get(),
        ];

        return view(
            'admin.dashboard',
            [
                'users' => $users,
                'notifications' => $notifications,
            ]
        );
    }
}
