<?php declare(strict_types = 1);

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;

/**
 * Class RegisterController
 *
 * @package App\Http\Controllers\Auth
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

    use RegistersUsers, ProfilesMethodsTrait;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return redirect(AUTH_HOME);
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
        $this->startProfiling(__FUNCTION__);

        $apiToken = str_random(60);
        $password = str_random(32);

        $this->addTrace('Creating User', __FUNCTION__, __LINE__);
        $user = User::create(
            [
                'name'                => null,
                'email'               => $data['email'],
                'api_token'           => $apiToken,
                'password'            => bcrypt($password),
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
        event(new UserRegistered($user, $password));

        $this->stopProfiling(__FUNCTION__);

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
                'email' => 'required|email|max:255|unique:users',
            ]
        );
    }
}
