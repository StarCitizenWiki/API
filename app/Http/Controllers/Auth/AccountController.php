<?php

namespace App\Http\Controllers\Auth;

use App\Events\URLShortened;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ShortURL\ShortURLController;
use App\Models\ShortURL\ShortURL;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    /**
     * @return View
     */
    public function show()
    {
        return view('auth.account.index')->with('user', Auth::user());
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete()
    {
        $user = Auth::user();
        Log::info('User deleted Account', [
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
    public function editAccount()
    {
        return view('auth.account.edit')->with('user', Auth::user());
    }

    /**
     * @return View
     */
    public function showURLs()
    {
        $user = Auth::user();
        return view('auth.account.shorturl.index')->with('urls', $user->shortURLs()->get());
    }

    public function showAddURLForm()
    {
        $user = Auth::user();
        return view('auth.account.shorturl.add')->with('user', $user);
    }

    public function addURL(Request $request)
    {
        $urlController = resolve(ShortURLController::class);

        try {
            $urlController->create($request);
        } catch (HashNameAlreadyAssignedException | URLNotWhitelistedException $e) {
            return redirect()->route('account_urls_add_form')->withErrors($e->getMessage());
        }

        return redirect()->route('account_urls_list');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteURL(int $id)
    {
        Auth::user()->shortURLs()->find($id)->delete();
        return back();
    }

    /**
     * @return View
     */
    public function editURL(int $id)
    {
        $url = ShortURL::find($id);
        return view('auth.account.shorturl.edit')->with('url', $url);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function patchURL(Request $request, int $id)
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

        ShortURL::updateShortURL([
            'id' => $id,
            'url' => $request->get('url'),
            'hash_name' => $request->get('hash_name'),
            'user_id' => Auth::id(),
        ]);

        return redirect('/account/urls');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function patchAccount(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'name' => 'present',
            'email' => 'required|min:3|email',
            'password' => 'present|min:8|confirmed'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if (!is_null($request->password)) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        Auth::logout();
        return redirect(AUTH_LOGIN);
    }
}
