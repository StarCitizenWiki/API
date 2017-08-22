<?php declare(strict_types = 1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AccountController
 *
 * @package App\Http\Controllers\User
 */
class AccountController extends Controller
{
    use ProfilesMethodsTrait;

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
    public function showAccountView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.auth.account.index')->with(
            'user',
            Auth::user()
        )->with(
            'request_count',
            Auth::user()->apiRequests()->where('created_at', '>', Carbon::now()->subMinute())->count()
        );
    }

    /**
     * Returns the Account Edit View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showEditAccountView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.auth.account.edit')->with(
            'user',
            Auth::user()
        );
    }

    /**
     * Returns the Account Deletion View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showDeleteAccountView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('api.auth.account.delete');
    }

    /**
     * Function to delete the associated User Account
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $user = Auth::user();
        Auth::logout();
        $user->delete();
        app('Log')::notice('User Account deleted');

        $this->stopProfiling(__FUNCTION__);

        return redirect(AUTH_HOME);
    }

    /**
     * Updates the current users account
     *
     * @param \Illuminate\Http\Request $request The Update Request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAccount(Request $request): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $user = Auth::user();
        $data = [];

        $this->validate(
            $request,
            [
                'name'                       => 'present',
                'email'                      => 'required|string|email|max:255|unique:users,email,'.$user->id,
                'password'                   => 'nullable|string|min:8|confirmed',
                'receive_notification_level' => 'required|int|between:-1,3',
            ]
        );

        $data['id'] = $user->id;
        $data['name'] = $request->get('name');
        $data['email'] = $request->get('email');
        if (!is_null($request->get('password')) && !empty($request->get('password'))) {
            $this->addTrace('Password changed', __FUNCTION__, __LINE__);
            $data['password'] = $request->get('password');
        }

        $this->addTrace('Updating User', __FUNCTION__, __LINE__);
        User::updateUser($data);

        if (array_key_exists('password', $data)) {
            Auth::logout();
            $this->addTrace('Password changed, logging out', __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return redirect()->route('auth_login_form');
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('account');
    }
}
