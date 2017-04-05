<?php

namespace App\Http\Controllers\Auth\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class AccountController
 *
 * @package App\Http\Controllers\Auth
 */
class AccountController extends Controller
{
    /**
     * Returns the Account Dashboard View
     *
     * @return View
     */
    public function showAccountView() : View
    {
        Log::debug('User requested Account View', ['user_id' => Auth::id()]);

        return view('auth.account.index')->with('user', Auth::user());
    }

    /**
     * Returns the Account Edit View
     *
     * @return View
     */
    public function showEditAccountView() : View
    {
        Log::debug('User requested Edit Account View', ['user_id' => Auth::id()]);

        return view('auth.account.edit')->with('user', Auth::user());
    }

    /**
     * Function to delete the associated User Account
     *
     * @return RedirectResponse
     */
    public function delete() : RedirectResponse
    {
        $user = Auth::user();
        Auth::logout();
        $user->delete();
        Log::info('Account deleted', [
            'id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect(AUTH_HOME);
    }

    /**
     * Updates the current users account
     *
     * @param Request $request The Update Request
     *
     * @return RedirectResponse
     */
    public function updateAccount(Request $request) : RedirectResponse
    {
        $user = Auth::user();
        $data = [];

        $this->validate($request, [
            'name' => 'present',
            'email' => 'required|min:3|email',
            'password' => 'present|min:8|confirmed',
        ]);

        $data['id'] = $user->id;
        $data['name'] = $request->get('name');
        $data['email'] = $request->get('email');
        if (!is_null($request->get('password')) &&
            !empty($request->get('password'))
        ) {
            $data['password'] = $request->get('password');
        }

        User::updateUser($data);

        if (array_key_exists('password', $data)) {
            Auth::logout();

            return redirect()->route('auth_login_form');
        }

        return redirect()->route('account');
    }
}
