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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    /**
     * @return View
     */
    public function showAccountView()
    {
        return view('auth.account.index')->with('user', Auth::user());
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete()
    {
        $user = Auth::user();
        Log::info('Account deleted', [
            'id' => $user->id,
            'email' => $user->email,
            'blacklisted' => $user->isBlacklisted()
        ]);
        $user->delete();
        Auth::logout();
        return redirect(AUTH_HOME);
    }

    /**
     * @return View
     */
    public function showEditAccountView()
    {
        return view('auth.account.edit')->with('user', Auth::user());
    }

    /**
     * @return View
     */
    public function showURLsView()
    {
        $user = Auth::user();
        return view('auth.account.shorturl.index')->with('urls', $user->shortURLs()->get());
    }

    public function showAddURLView()
    {
        $user = Auth::user();
        return view('auth.account.shorturl.add')->with('user', $user);
    }

    public function addURL(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|active_url|max:255|unique:short_urls',
            'hash_name' => 'nullable|alpha_dash|max:32|unique:short_urls'
        ]);

        try {
            $url = ShortURL::createShortURL([
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name'),
                'user_id' => Auth::id()
            ]);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('account_urls_add_form')->withErrors($e->getMessage());
        }

        event(new URLShortened($url));

        return redirect()->route('account_urls_list')->with('hash_name', $url->hash_name);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteURL(int $id)
    {
        try {
            $url = Auth::user()->shortURLs()->findOrFail($id);
            Log::info('URL deleted', [
                'user_id' => Auth::id(),
                'url_id' => $url->id,
                'url' => $url->url,
                'hash_name' => $url->hash_name
            ]);
            $url->delete();
        } catch (ModelNotFoundException $e) {
            Log::info('User tried to delete unowned ShortURL', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'url_id' => $id
            ]);
        }
        return back();
    }

    /**
     * @return View
     */
    public function showEditURLView(int $id)
    {
        $url = ShortURL::find($id);
        return view('auth.account.shorturl.edit')->with('url', $url);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateURL(Request $request, int $id)
    {
        if ($request->get('user_id') != Auth::id() ||
            Auth::user()->shortURLs()->find($id)->count() === 0)
        {
            Log::notice('User tried to forge ShortURL edit request', [
                'user_id' => Auth::id(),
                'provided_id' => $request->get('user_id'),
                'url_id' => $id,
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name')
            ]);
            return abort(401);
        }

        $this->validate($request, [
            'url' => 'required|active_url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        try {
            ShortURL::updateShortURL([
                'id' => $id,
                'url' => $request->get('url'),
                'hash_name' => $request->get('hash_name'),
                'user_id' => Auth::id(),
            ]);
        } catch (URLNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('account_urls_list');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        $data = [];

        $this->validate($request, [
            'name' => 'present',
            'email' => 'required|min:3|email',
            'password' => 'present|min:8|confirmed'
        ]);

        $data['id'] = $user->id;
        $data['name'] = $request->get('name');
        $data['email'] = $request->get('email');
        if (!is_null($request->get('password')) && !empty($request->get('password'))) {
            $data['password'] = $request->get('password');
        }

        try {
            User::updateUser($data);
        } catch (ModelNotFoundException $e) {
            Log::warning('['.__METHOD__.'] Account not found', [
                'id' => $data['id']
            ]);
            return back()->withErrors('Error updating Account');
        }

        if (array_key_exists('password', $data)) {
            Auth::logout();
            return redirect(AUTH_LOGIN);
        } else {
            return redirect()->route('account');
        }
    }
}
