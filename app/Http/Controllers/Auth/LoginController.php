<?php declare(strict_types = 1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    use AuthenticatesUsers, ProfilesMethodsTrait;

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
        app('Log')::info('User with ID: '.Auth::id().' logged out');

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
            app('Log')::notice("Blacklisted User with ID: {$user->id} tried to login");
            Auth::logout();

            return redirect()->route('auth_login_form')->withErrors('Account is blacklisted');
        }

        app('Log')::info("User with ID: {$user->id} logged in");

        return null;
    }
}
