<?php declare(strict_types = 1);

namespace Tests\Feature\Controller;

use App\Http\Controllers\User\Auth\RegisterController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class RegisterControllerTest
 * @package Tests\Feature\Controller
 */
class RegisterControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @var  RegisterController */
    private $controller;

    /**
     * Resolve the Controller
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = resolve(RegisterController::class);
    }

    /**
     * @covers \App\Http\Controllers\User\Auth\RegisterController::register()
     * @covers \App\Events\UserRegistered
     */
    public function testRegistration()
    {
        $password = str_random(8);

        $response = $this->post(
            'register',
            [
                'email'                 => str_random(5).'@'.str_random(5).'.de',
                'name'                  => str_random(5),
                'password'              => $password,
                'password_confirmation' => $password,
            ]
        );

        $response->assertStatus(302);
        $response->assertRedirect('account');
    }

    /**
     * @covers \App\Http\Controllers\User\Auth\RegisterController::create()
     * @throws \Exception
     */
    public function testCreate()
    {
        $name = str_random(6);
        $email = strtolower($name).'@'.strtolower($name).'.de';
        $user = $this->controller->create(
            [
                'name'     => $name,
                'email'    => $email,
                'password' => bcrypt($name),
            ]
        );

        $this->assertEquals($email, $user->email);
    }

    /**
     * @covers \App\Http\Controllers\User\Auth\RegisterController::showRegistrationForm()
     */
    public function testRegistrationFormView()
    {
        $response = $this->get('register');
        $response->assertStatus(200);
    }
}
