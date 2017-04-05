<?php

namespace App\Http\Controllers\Auth\Account;

use App\Events\URLShortened;
use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

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
        return view('auth.account.index')->with('user', Auth::user());
    }

    /**
     * Returns the Account Edit View
     *
     * @return View
     */
    public function showEditAccountView() : View
    {
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

        try {
            User::updateUser($data);
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] Account not found', [
                'id' => $data['id'],
            ]);

            return back()->withErrors('Error updating Account')->withInput(Input::all());
        }

        if (array_key_exists('password', $data)) {
            Auth::logout();

            return redirect()->route('auth_login_form');
        }

        return redirect()->route('account');
    }
}
