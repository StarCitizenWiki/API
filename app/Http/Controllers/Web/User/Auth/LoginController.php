<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Auth;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginController.
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
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = '/account';

    /**
     * @var \App\Contracts\Web\User\AuthRepositoryInterface
     */
    private $authRepository;

    /**
     * Create a new controller instance.
     *
     * @param \App\Contracts\Web\User\AuthRepositoryInterface $authRepository
     */
    public function __construct(AuthRepositoryInterface $authRepository)
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'logout']);
        $this->authRepository = $authRepository;
    }

    /**
     * Get the path that we should redirect once logged out.
     * Adaptable to user needs.
     *
     * @return string
     */
    public function logoutToPath()
    {
        return '/';
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('user.auth.login');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return $this->authRepository->startAuth();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(Request $request)
    {
        $user = $this->authRepository->getUserFromProvider($request);

        $authUser = $this->authRepository->getOrCreateLocalUser($user, 'mediawiki');

        Auth::login($authUser);

        return $this->authenticated();
    }

    /**
     * @return string
     */
    public function getRedirectTo(): string
    {
        if (Auth::user()->isAdmin()) {
            return '/dashboard';
        }

        return $this->redirectTo;
    }

    /**
     * Redirect to Login Form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('web.user.auth.login');
    }

    /**
     * Redirect to Intended Route or Account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated()
    {
        return redirect()->intended($this->getRedirectTo());
    }
}
