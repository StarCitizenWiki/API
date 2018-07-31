<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Api\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AccountController
 */
class AccountController extends Controller
{
    const PASSWORD = 'password';

    /**
     * AccountController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Returns the Account Dashboard View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $user = Auth::user();

        return view(
            'user.account.index',
            [
                'user' => $user,
                'notification_level_text' => Notification::NOTIFICATION_LEVEL_TYPES[$user->receive_notification_level],
            ]
        );
    }

    /**
     * Returns the Account Edit View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.account.edit',
            [
                'user' => Auth::user(),
            ]
        );
    }

    /**
     * Returns the Account Deletion View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function delete(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('user.account.delete');
    }

    /**
     * Function to delete the associated User Account
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function destroy(): RedirectResponse
    {
        $user = Auth::user();
        app('Log')::notice("User Account ({$user->name}:{$user->id}) deleted");
        $user->delete();
        Auth::logout();

        return redirect()->route('web.api.index');
    }

    /**
     * Updates the current users account
     *
     * @param \Illuminate\Http\Request $request The Update Request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $shouldLogout = false;
        /** @var \App\Models\Account\User $user */
        $user = Auth::user();

        $data = $request->validate(
            [
                'name' => 'required|string|min:3|max:200',
                'email' => 'required|string|email|max:200|unique:users,email,'.$user->id,
                'password' => 'nullable|string|min:8|confirmed',
                'receive_notification_level' => 'required|int|between:-1,3',
            ]
        );

        if (isset($data[self::PASSWORD]) && null !== $data[self::PASSWORD]) {
            $shouldLogout = true;
            $data[self::PASSWORD] = bcrypt($data[self::PASSWORD]);
        } else {
            unset($data[self::PASSWORD]);
        }

        $user->update($data);

        if ($shouldLogout) {
            Auth::logout();

            return redirect()->route('web.user.auth.login_form')->withInput(
                [
                    'email' => $user->email,
                ]
            );
        }

        return redirect()->route(
            'web.user.account.index',
            [
                'message' => __('crud.updated', ['type' => 'Account']),
            ]
        );
    }
}
