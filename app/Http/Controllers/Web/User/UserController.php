<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Account\User\User;
use Illuminate\Auth\Access\AuthorizationException;
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
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.users.view');

        return view(
            'web.users.index',
            [
                'users' => User::query()->withCount('changelogs')->get(),
            ]
        );
    }

    /**
     * Edit Admin
     *
     * @param User $user
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(User $user): View
    {
        $this->authorize('web.users.update');

        return view(
            'web.users.edit',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * Update (Block/Restore) Admin
     *
     * @param Request $request
     * @param User    $user
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('web.users.update');

        if ($request->has('block')) {
            return $this->block($user);
        }

        $data = $request->validate(
            [
                self::API_TOKEN => "required|min:60|max:60|string|unique:users,api_token,{$user->id}",
                'language' => 'required|in:en,de',
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
                    'language' => $request->get('language'),
                ]
            );
        }

        return redirect(route('web.users.edit', $user->getRouteKey()))->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Benutzer')]),
                ],
            ]
        );
    }

    /**
     * @param User $user
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    private function block(User $user): RedirectResponse
    {
        $this->authorize('web.users.delete');

        $user->sessions()->delete();
        $user->blocked = true;
        $user->save();

        return redirect(route('web.users.edit', $user->getRouteKey()))->withMessages(
            [
                'warning' => [
                    __('crud.blocked', ['type' => __('Benutzer')]),
                ],
            ]
        );
    }
}
