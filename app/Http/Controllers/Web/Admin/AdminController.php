<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account\Admin\Admin;
use App\Models\Account\User\User;
use App\Models\Api\Notification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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
     * View all Admins
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.admin.admins.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.admins.index',
            [
                'admins' => Admin::all(),
            ]
        );
    }

    /**
     * Edit Admin
     *
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Admin $admin): View
    {
        $this->authorize('web.admin.admins.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.admins.edit',
            [
                'admin' => $admin,
            ]
        );
    }

    /**
     * Update (Block) Admin
     *
     * @param \Illuminate\Http\Request        $request
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $this->authorize('web.admin.admins.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $admin->blocked = 1;
        $admin->save();

        return redirect(route('web.admin.admins.edit', $admin->getRouteKey()));
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
        $this->authorize('web.admin.dashboard.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

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
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function acceptLicenseView()
    {
        $this->authorize('web.admin.accept_license');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        if (optional(Auth::guard('admin')->user()->settings)->editor_license_accepted === true) {
            return redirect()->route('web.admin.dashboard');
        }

        return view('admin.accept_license');
    }

    /**
     * Update Admin to accept Editor Licence
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function acceptLicense(Request $request)
    {
        $this->authorize('web.admin.accept_license');
        app('Log')::debug(make_name_readable(__FUNCTION__));

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
