<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Account;

use App\Models\Account\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    use RefreshDatabase;

    private $user;

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::index
     */
    public function testIndexView()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.account.index'));

        $response->assertOk()->assertSee($this->user->name);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::delete
     */
    public function testDeleteView()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.account.delete'), []);

        $response->assertOk()->assertSee(__('Löschen'));
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::destroy
     */
    public function testDestroy()
    {
        $response = $this->actingAs($this->user)->delete(route('web.user.account.destroy'), []);

        $response->assertRedirect(route('web.api.index'));
        $this->assertGuest();
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::edit
     */
    public function testEditView()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.account.edit'));

        $response->assertOk()->assertSee(__('Speichern'));
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::update
     */
    public function testUpdate()
    {
        $response = $this->followingRedirects()->actingAs($this->user)->patch(
            route('web.user.account.update'),
            [
                'name' => 'UpdatedName',
                'email' => 'a'.str_random(5).'@star-citizen.wiki',
                'receive_notification_level' => 1,
                'password' => null,
                'password_confirmation' => null,
            ]
        );

        $response->assertSee('UpdatedName')->assertSee(__('Account'));
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::update
     */
    public function testUpdateWithPassword()
    {
        $email = 'a'.str_random(5).'@star-citizen.wiki';

        $response = $this->followingRedirects()->actingAs($this->user)->patch(
            route('web.user.account.update'),
            [
                'name' => 'UpdatedName',
                'email' => $email,
                'password' => 'testpassword',
                'password_confirmation' => 'testpassword',
                'receive_notification_level' => 1,
            ]
        );

        $response->assertSee($email)->assertSee(__('Login'));
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::index
     */
    public function testBlockedUserAccessAccount()
    {
        $user = factory(User::class)->states('blocked')->create();

        $response = $this->actingAs($user)->get(route('web.user.account.index'));
        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::index
     */
    public function testBlockedUserAccessApiIndex()
    {
        $user = factory(User::class)->states('blocked')->create();

        $response = $this->actingAs($user)->get(route('web.api.index'));
        $response->assertStatus(403);
    }

    /**
     * Creates a User in the DB
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }
}
