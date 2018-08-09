<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\Account\User\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AdminUserController
 */
class UserController extends Controller
{
    const WEB_ADMIN_USERS_INDEX = 'web.admin.users.index';
    const PASSWORD = 'password';

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:admin');
    }

    /**
     * Returns the View with all Users listed
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.admin.users.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.users.index',
            [
                'users' => User::withTrashed()->orderBy('deleted_at')->simplePaginate(100),
            ]
        );
    }

    /**
     * Returns the View to Edit a User by ID
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Routing\Redirector
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(User $user)
    {
        $this->authorize('web.admin.users.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.users.edit',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * Deletes a User by ID
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('web.admin.users.delete');
        app('Log')::notice("Account {$user->name} ({$user->id}) deleted by ".Auth::id());

        $user->delete();

        return redirect()->route(self::WEB_ADMIN_USERS_INDEX);
    }

    /**
     * Restores a User by ID
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function restore(User $user): RedirectResponse
    {
        $this->authorize('web.admin.users.update');
        app('Log')::notice("Restored Account with ID: {$user->id}");

        $user->restore();

        return redirect()->route(self::WEB_ADMIN_USERS_INDEX);
    }

    /**
     * Updates a User by ID
     *
     * @param \Illuminate\Http\Request      $request Update Request
     * @param \App\Models\Account\User\User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('web.admin.users.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $this->validate(
            $request,
            [
                'name' => 'present|string',
                'requests_per_minute' => 'required|integer',
                'email' => 'required|email|min:3',
                'state' => 'required|int|between:0,2',
                'notes' => 'nullable|string',
                self::PASSWORD => 'nullable|string|min:3',
            ]
        );

        if (isset($data[self::PASSWORD]) && null !== $data[self::PASSWORD]) {
            $data[self::PASSWORD] = bcrypt($data[self::PASSWORD]);
        } else {
            unset($data[self::PASSWORD]);
        }

        $user->update($data);

        return redirect()->route(self::WEB_ADMIN_USERS_INDEX);
    }
}
