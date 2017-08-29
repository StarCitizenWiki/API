<?php declare(strict_types = 1);

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Class RegisterController
 *
 * @package App\Http\Controllers\User\Auth
 */
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return View
     */
    public function showRegistrationForm(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('user.auth.register');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data UserData
     *
     * @return \App\Models\User
     */
    public function create(array $data)
    {
        $apiToken = str_random(60);
        $user = User::create(
            [
                'name'                => $data['name'],
                'email'               => $data['email'],
                'api_token'           => $apiToken,
                'password'            => bcrypt($data['password']),
                'requests_per_minute' => 60,
                'last_login'          => date('Y-m-d H:i:s'),
            ]
        );

        app('Log')::notice(
            'Account created',
            [
                'id'    => $user->id,
                'email' => $user->email,
            ]
        );

        return $user;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data Data to validate
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data,
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]
        );
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
