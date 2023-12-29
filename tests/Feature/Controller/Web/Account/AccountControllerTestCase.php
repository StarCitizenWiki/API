<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Account;

use App\Http\Controllers\Web\Account\AccountController;
use App\Models\Account\User\User;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\UserTestCase;

/**
 * @covers \App\Http\Controllers\Web\Account\AccountController
 */
class AccountControllerTestCase extends UserTestCase
{
    /**
     * Index Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\Account\AccountController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.account.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('web.account.index')
                ->assertSee($this->user->username)
                ->assertSee(__('Speichern'));
        }
    }


    /**
     * Update Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\Account\AccountController::update
     */
    public function testUpdate()
    {
        $response = $this->followingRedirects()->actingAs($this->user)->patch(
            route('web.account.update', $this->user->getRouteKey()),
            [
                'api_notifications' => 'on',
                'language' => 'de'
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['update']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('web.account.index')
                ->assertSee(__('crud.updated', ['type' => __('Einstellungen')]))
                ->assertSee($this->user->username);

            $this->assertTrue(User::find($this->user->id)->receiveApiNotifications());
        }
    }

}
