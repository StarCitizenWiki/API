<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Account;

use App\Models\Account\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AccountControllerTest
 */
class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::showAccountView
     * @covers \App\Http\Middleware\RedirectIfAuthenticated
     */
    public function testAccountView()
    {
        $response = $this->actingAs($this->user)
            ->get(route('web.user.account.index'));

        $response->assertStatus(200)
            ->assertSee($this->user->name);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::delete
     */
    public function testDeleteAccountView()
    {
        $response = $this->actingAs($this->user)
            ->get(route('web.user.account.delete'), []);

        $response->assertStatus(200)
            ->assertSee(__('Löschen'));
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::destroy
     */
    public function testDeleteAccount()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('web.user.account.destroy'), []);

        $response->assertRedirect(route('web.api.index'));
        $this->assertGuest();
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::showEditAccountView
     */
    public function testAccountEditFormView()
    {
        $response = $this->actingAs($this->user)
            ->get(route('web.user.account.edit'));

        $response->assertStatus(200)
            ->assertSee(__('Speichern'));
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::updateAccount
     * @covers \App\Http\Middleware\VerifyCsrfToken
     */
    public function testUpdateAccount()
    {
        $response = $this->followingRedirects()
            ->actingAs($this->user)
            ->patch(
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
     * @covers \App\Http\Controllers\Web\User\AccountController::updateAccount
     * @covers \App\Http\Middleware\VerifyCsrfToken
     */
    public function testUpdateAccountWithPassword()
    {
        $email = 'a'.str_random(5).'@star-citizen.wiki';

        $response = $this->followingRedirects()
            ->actingAs($this->user)
            ->patch(
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
        $this->assertGuest();
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::index
     * @covers \App\Http\Middleware\CheckUserState
     */
    public function testBlockedUserAccessAccount()
    {
        $user = factory(User::class)->states('blocked')->create();

        $response = $this->actingAs($user)->get(route('web.user.account.index'));
        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\AccountController::index
     * @covers \App\Http\Middleware\CheckUserState
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
