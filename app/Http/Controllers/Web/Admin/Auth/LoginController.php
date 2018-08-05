<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Hesto\MultiAuth\Traits\LogsoutGuard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth1\User;

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

    use AuthenticatesUsers, LogsoutGuard {
        LogsoutGuard::logout insteadof AuthenticatesUsers;
    }

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = '/admin/dashboard';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('admin.guest', ['except' => 'logout']);
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
        return Socialite::with('mediawiki')->stateless(false)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::with('mediawiki')->stateless(false)->user();

        $authUser = $this->findOrCreateAdmin($user, 'mediawiki');
        Auth::guard('admin')->login($authUser);

        return redirect($this->redirectTo);
    }

    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param  \SocialiteProviders\Manager\OAuth1\User $user     Socialite user object
     * @param  string                                  $provider Social auth provider
     *
     * @return  \App\Models\Account\Admin\Admin
     */
    public function findOrCreateAdmin($user, $provider)
    {
        $authUser = Admin::where('provider_id', $user->id)->first();

        if ($authUser) {
            $this->syncAdminGroups($user, $authUser);

            return $authUser;
        }

        /** @var \App\Models\Account\Admin\Admin $admin */
        $admin = Admin::create(
            [
                'username' => $user->username,
                'blocked' => $user->blocked,
                'provider_id' => $user->id,
                'provider' => $provider,
            ]
        );

        $this->syncAdminGroups($user, $admin);

        return $admin;
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
     * Sync provided Groups
     *
     * @param \SocialiteProviders\Manager\OAuth1\User $oauthUser
     * @param \App\Models\Account\Admin\Admin         $admin
     */
    private function syncAdminGroups(User $oauthUser, Admin $admin): void
    {
        $groups = $oauthUser->user['groups'] ?? null;

        if (is_array($groups)) {
            $groupIDs = AdminGroup::select('id')->whereIn('name', $groups)->get();

            $admin->groups()->sync($groupIDs);
        }
    }
}
