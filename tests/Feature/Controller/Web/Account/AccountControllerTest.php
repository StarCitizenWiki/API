<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\Account;

use App\Http\Controllers\Web\User\Account\AccountController;
use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Tests\TestCase;

/**
 * Class AccountControllerTest
 *
 * @covers \App\Http\Middleware\VerifyCsrfToken
 * @covers \App\Http\Middleware\CheckUserState
 * @covers \App\Http\Middleware\Web\User\RedirectIfAuthenticated
 */
class AccountControllerTest extends TestCase
{
    private $user;

    /**
     * @covers \App\Http\Controllers\Web\User\Account\AccountController::show
     */
    public function testIndexView()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.account.show'));
        $response->assertOk()->assertSee($this->user->name);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Account\AccountController::show
     */
    public function testBlockedUserAccessAccount()
    {
        $user = factory(User::class)->states('blocked')->create();

        $response = $this->actingAs($user)->get(route('web.user.account.show'));
        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Account\AccountController::show
     */
    public function testBlockedUserAccessApiIndex()
    {
        $user = factory(User::class)->states('blocked')->create();

        $response = $this->actingAs($user)->get(route('web.api.index'));
        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Account\AccountController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(AccountController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(AccountController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }

    /**
     * Creates a User in the DB
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createUserGroups();
        $this->user = factory(User::class)->create();
        $this->user->groups()->sync(UserGroup::where('name', 'user')->first()->id);
    }
}
