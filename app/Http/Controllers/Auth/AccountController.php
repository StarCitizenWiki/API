<?php

namespace App\Http\Controllers\Auth;

use App\Events\URLShortened;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Returns the Account Edit View
     *
     * @return View
     */
    public function showEditAccountView() : View
    {
        return view('auth.account.edit')->with('user', Auth::user());
    }

    /**
     * Returns the View which lists all associated ShortURLs
     *
     * @return View
     */
    public function showURLsView() : View
    {
        $user = Auth::user();

        return view('auth.account.shorturl.index')->with('urls', $user->shortURLs()->get());
    }

    /**
     * Returns the add short url view
     *
     * @return View
     */
    public function showAddURLView() : View
    {
        $user = Auth::user();

        return view('auth.account.shorturl.add')->with('user', $user);
    }

    /**
     * Validates the url add Request and creates a new ShortURL
     *
     * @param Request $request The Request
     *
     * @return Redirect | RedirectResponse
     */
    public function addURL(Request $request)
    {
        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
        ];

        $rules = [
            'url' => 'required|active_url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls',
        ];

        validate_array($data, $rules, $request);

        try {
            $url = ShortURL::createShortURL([
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => Auth::id(),
            ]);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('account_urls_add_form')
                             ->withErrors($e->getMessage());
        }

        event(new URLShortened($url));

        return redirect()->route('account_urls_list')
                         ->with('hash_name', $url->hash_name);
    }

    /**
     * Deletes a Users ShortURL
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteURL(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'id' => 'required|exists:short_urls|int',
        ]);

        try {
            $url = Auth::user()->shortURLs()->findOrFail($request->id);
            Log::info('URL deleted', [
                'user_id' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name,
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::info('User tried to delete unowned ShortURL', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'url_id' => $request->id,
            ]);
        }

        return back();
    }

    /**
     * Returns the Edit ShortURL View
     *
     * @param int $id The ShortURL ID to edit
     *
     * @return View | RedirectResponse
     */
    public function showEditURLView(int $id)
    {
        try {
            $url = Auth::user()->shortURLs()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::info('User tried to edit unowned ShortURL', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'url_id' => $id,
            ]);

            return \redirect()->route('account_urls_list');
        }

        return view('auth.account.shorturl.edit')->with('url', $url);
    }

    /**
     * Updates a ShortURL by its ID
     *
     * @param Request $request The Update Request
     *
     * @return RedirectResponse
     */
    public function updateURL(Request $request) : RedirectResponse
    {
        if (Auth::user()->shortURLs()->find($request->id)->count() === 0) {
            Log::warning('User tried to forge ShortURL edit request', [
                'user_id' => Auth::id(),
                'provided_id' => $request->get('user_id'),
                'url_id' => $request->id,
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name'),
            ]);

            return abort(401, 'No permission');
        }

        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
        ];

        $rules = [
            'url' => 'required|active_url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
        ];

        validate_array($data, $rules, $request);

        try {
            ShortURL::updateShortURL([
                'id' => $request->id,
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => Auth::id(),
            ]);
        } catch (URLNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('account_urls_list');
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

            return back()->withErrors('Error updating Account');
        }

        if (array_key_exists('password', $data)) {
            Auth::logout();

            return redirect()->route('auth_login_form');
        }

        return redirect()->route('account');
    }
}
