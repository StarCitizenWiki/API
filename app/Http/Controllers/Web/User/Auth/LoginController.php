<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Auth;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
     * @var AuthRepositoryInterface
     */
    private AuthRepositoryInterface $authRepository;

    /**
     * Create a new controller instance.
     *
     * @param AuthRepositoryInterface $authRepository
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
     * @return Factory|View
     */
    public function showLoginForm()
    {
        return view('user.auth.login');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return $this->authRepository->startAuth();
    }

    /**
     * Obtain the user information from the Provider.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function handleProviderCallback(Request $request): RedirectResponse
    {
        $user = $this->authRepository->getUserFromProvider($request);

        $authUser = $this->authRepository->getOrCreateLocalUser($user, 'mediawiki');

        Auth::login($authUser);

        return $this->authenticated();
    }

    /**
     * Redirect to Intended Route or Account.
     *
     * @return RedirectResponse
     */
    protected function authenticated(): RedirectResponse
    {
        return redirect()->intended($this->getRedirectTo());
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
     * @param Request $request
     *
     * @return RedirectResponse
     */
    protected function loggedOut(Request $request): RedirectResponse
    {
        return redirect()->route('web.user.auth.login');
    }
}
