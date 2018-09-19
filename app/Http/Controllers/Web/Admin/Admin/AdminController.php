<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account\Admin\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
                'admins' => Admin::query()->withCount('changelogs')->get(),
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
     * Update (Block/Restore) Admin
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

        if ($request->has('restore')) {
            $admin->blocked = 0;
        } else {
            $admin->blocked = 1;
        }
        $admin->save();

        return redirect(route('web.admin.admins.edit', $admin->getRouteKey()));
    }
}
