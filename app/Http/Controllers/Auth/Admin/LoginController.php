<?php declare(strict_types = 1);

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use Hesto\MultiAuth\Traits\LogsoutGuard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

/**
 * Class LoginController
 *
 * @package App\Http\Controllers\AdminAuth
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

    private const API_URI = 'api.php?action=usercheck&format=json';

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = '/admin/dashboard';

    private $backendError = false;

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
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
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
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $guzzleClient = new Client(
            [
                'base_uri' => SCW_URL,
                'timeout'  => 10.0,
            ]
        );

        try {
            $response = $guzzleClient->request(
                'POST',
                self::API_URI,
                [
                    'form_params' => $this->credentials($request),
                ]
            );

        } catch (ConnectException | Exception $e) {
            $response = new Response(500, [], '{}');
            $this->backendError = true;
        }

        $response = json_decode($response->getBody()->getContents(), true);

        if (array_key_exists('usercheck', $response) && 'ok' === $response['usercheck']['status']) {
            return $this->guard()->attempt(
                [
                    'username' => $request->get('username'),
                    'password' => ADMIN_INTERNAL_PASSWORD,
                ],
                false
            );
        }

        return false;
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if ($this->backendError) {
            $errors = ['Backend Error'];
        } else {
            $errors = [$this->username() => trans('auth.failed')];
        }

        return redirect()->back()
            ->withInput($request->only($this->username()))
            ->withErrors($errors);
    }
}
