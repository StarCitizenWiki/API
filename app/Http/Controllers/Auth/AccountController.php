<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
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

    public function showEditForm()
    {
        return view('auth.account.edit')->with('user', Auth::user());
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
