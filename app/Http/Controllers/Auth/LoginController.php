<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class LoginController
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = AUTH_ACCOUNT;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request Login Request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->logger->debug('User logged out', [
            'user_id' => Auth::id(),
        ]);

        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect()->route('api_index');
    }

    /**
     * Checks if a User is blacklisted if so returns an error
     *
     * @param \Illuminate\Http\Request $request Login Request
     * @param mixed                    $user    Authenticated User
     *
     * @return RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->isBlacklisted()) {
            $this->logger->info('Blacklisted User tried to login', [
                'user_id' => $user->id,
            ]);
            Auth::logout();

            return redirect()->route('auth_login_form')
                             ->withErrors('Account is blacklisted');
        }

        return null;
    }
}
