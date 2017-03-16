<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function show()
    {
        return view('auth.account.index')->with('user', Auth::user());
    }

    public function delete()
    {
        Auth::user()->delete();
        Auth::logout();
        return redirect(AUTH_HOME);
    }

    public function editAccount()
    {
        return view('auth.account.edit')->with('user', Auth::user());
    }

    public function showURLs()
    {
        $user = Auth::user();
        return view('auth.account.shorturl.index')->with('urls', $user->shortURLs()->get());
    }

    public function deleteURL(int $id)
    {
        Auth::user()->shortURLs()->find($id)->delete();
        return back();
    }

    public function editURL(int $id)
    {
        $url = ShortURL::find($id);
        return view('auth.account.shorturl.edit')->with('url', $url);
    }

    public function patchURL(Request $request, int $id)
    {
        if ($request->get('user_id') != Auth::id() ||
            Auth::user()->shortURLs()->find($id)->count() === 0)
        {
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
