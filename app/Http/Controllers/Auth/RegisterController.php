<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class RegisterController
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

    use RegistersUsers;

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
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        Log::debug('Registration Form requested', [
            'method' => __METHOD__,
        ]);

        return redirect(AUTH_HOME);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data UserData
     *
     * @return User
     */
    public function create(array $data)
    {
        $api_token = str_random(60);
        $password = str_random(32);

        $user = User::create([
            'name' => null,
            'email' => $data['email'],
            'api_token' => $api_token,
            'password' => bcrypt($password),
            'requests_per_minute' => 60,
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        Log::info('Account created', [
            'id' => $user->id,
            'email' => $user->email,
        ]);

        event(new UserRegistered($user, $password));

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
        return Validator::make($data, [
            'email' => 'required|email|max:255|unique:users',
        ]);
    }
}
