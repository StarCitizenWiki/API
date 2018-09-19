<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Contracts\Web\Admin\AuthRepositoryInterface;
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
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = '/admin/dashboard';

    /**
     * @var \App\Contracts\Web\Admin\AuthRepositoryInterface
     */
    private $authRepository;

    /**
     * Create a new controller instance.
     *
     * @param \App\Contracts\Web\Admin\AuthRepositoryInterface $authRepository
     */
    public function __construct(AuthRepositoryInterface $authRepository)
    {
        parent::__construct();
        $this->middleware('admin.guest', ['except' => 'logout']);
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
        return '/admin/login';
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
        return view('admin.auth.login');
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback()
    {
        $user = $this->authRepository->getUserFromProvider();
        $authUser = $this->authRepository->getOrCreateLocalUser($user, 'mediawiki');

        Auth::guard('admin')->login($authUser);

        return $this->authenticated();
    }

    /**
     * Redirect to Login Form
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('web.admin.auth.login');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Redirect to Accept Licence View if Editor has not accepted the licence
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated()
    {
        /** @var \App\Models\Account\Admin\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $accepted = optional($admin->settings)->editor_license_accepted ?? false;

        if ($admin->isEditor() && !$accepted) {
            return redirect()->route('web.admin.accept_license_view');
        }

        return redirect()->intended($this->redirectTo);
    }
}
