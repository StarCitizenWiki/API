<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account\User\User;
use App\Models\Api\Notification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    /**
     * View to Accept Editor Licence
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function acceptLicenseView()
    {
        return view('admin.accept_license');
    }

    /**
     * Update Admin to accept Editor Licence
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function acceptLicense(Request $request)
    {
        /** @var \App\Models\Account\Admin\Admin $admin */
        $admin = Auth::guard('admin')->user();
        $admin->settings()->updateOrCreate(
            [
                'editor_license_accepted' => true,
            ]
        );

        return redirect()->route('web.admin.dashboard');
    }
}
