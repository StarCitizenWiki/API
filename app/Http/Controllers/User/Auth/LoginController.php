<?php declare(strict_types = 1);

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginController
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
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('user.auth.login');
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
        app('Log')::info('User with ID: '.Auth::id().' logged out');

        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect('/');
    }

    /**
     * Checks if a User is blacklisted if so returns an error
     *
     * @param \Illuminate\Http\Request $request Login Request
     * @param mixed                    $user    Authenticated User
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->isBlocked()) {
            app('Log')::notice("Blacklisted User with ID: {$user->id} tried to login");
            Auth::logout();

            return redirect()->route('auth.login_form')->withErrors('Account is blacklisted');
        }

        app('Log')::info("User with ID: {$user->id} logged in");

        return null;
    }

    /**
     * Where to redirect users after registration.
     *
     * @return string
     */
    protected function redirectTo()
    {
        return route('account');
    }
}
