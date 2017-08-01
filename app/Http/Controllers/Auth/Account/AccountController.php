<?php declare(strict_types = 1);

namespace App\Http\Controllers\Auth\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AccountController
 *
 * @package App\Http\Controllers\Auth
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
     * @return View
     */
    public function showAccountView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('auth.account.index')->with(
            'user',
            Auth::user()
        );
    }

    /**
     * Returns the Account Edit View
     *
     * @return View
     */
    public function showEditAccountView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('auth.account.edit')->with(
            'user',
            Auth::user()
        );
    }

    /**
     * Function to delete the associated User Account
     *
     * @return RedirectResponse
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
     * @param Request $request The Update Request
     *
     * @return RedirectResponse
     */
    public function updateAccount(Request $request): RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $user = Auth::user();
        $data = [];

        $this->validate(
            $request,
            [
                'name'     => 'present',
                'email'    => 'required|min:3|email',
                'password' => 'nullable|min:8|confirmed',
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
