<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\Account;

use App\Http\Controllers\Web\User\Account\AccountController;
use App\Models\Account\User\User;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * @covers \App\Http\Controllers\Web\User\Account\AccountController
 */
class AccountControllerTestCase extends UserTestCase
{
    /**
     * Index Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\User\Account\AccountController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.account.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.account.index')
                ->assertSee($this->user->username)
                ->assertSee(__('Speichern'));
        }
    }


    /**
     * Update Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\User\Account\AccountController::update
     */
    public function testUpdate()
    {
        $response = $this->followingRedirects()->actingAs($this->user)->patch(
            route('web.user.account.update', $this->user->getRouteKey()),
            [
                'api_notifications' => 'on',
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['update']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.account.index')
                ->assertSee(__('crud.updated', ['type' => __('Einstellungen')]))
                ->assertSee($this->user->username);

            $this->assertTrue(User::find($this->user->id)->receiveApiNotifications());
        }
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
}
