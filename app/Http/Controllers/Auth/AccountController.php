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
        return view('auth.account');
    }

    public function delete()
    {
        User::destroy(Auth::user()->id);
        Auth::logout();
        return redirect(AUTH_HOME);
    }
}
