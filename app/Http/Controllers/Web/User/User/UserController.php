<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\User;

use App\Http\Controllers\Controller;
use App\Models\Account\User\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class AdminController
 */
class UserController extends Controller
{
    private const API_TOKEN = 'api_token';

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
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
        $this->authorize('web.user.users.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.users.index',
            [
                'users' => User::query()->withCount('changelogs')->get(),
            ]
        );
    }

    /**
     * Edit Admin
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(User $user): View
    {
        $this->authorize('web.user.users.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.users.edit',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * Update (Block/Restore) Admin
     *
     * @param \Illuminate\Http\Request      $request
     * @param \App\Models\Account\User\User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('web.user.users.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        if ($request->has('block')) {
            return $this->block($user);
        }

        $data = $request->validate(
            [
                self::API_TOKEN => "required|min:60|max:60|string|unique:users,api_token,{$user->id}",
            ]
        );

        $user->update(
            [
                self::API_TOKEN => $data[self::API_TOKEN],
            ]
        );

        if ($request->has('no_api_throttle')) {
            $user->settings()->updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'no_api_throttle' => true,
                ]
            );
        }

        return redirect(route('web.user.users.edit', $user->getRouteKey()))->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Benutzer')]),
                ],
            ]
        );
    }

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function block(User $user): RedirectResponse
    {
        $this->authorize('web.user.users.delete');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $user->sessions()->delete();
        $user->blocked = true;
        $user->save();

        return redirect(route('web.user.users.edit', $user->getRouteKey()))->withMessages(
            [
                'warning' => [
                    __('crud.blocked', ['type' => __('Benutzer')]),
                ],
            ]
        );
    }
}
