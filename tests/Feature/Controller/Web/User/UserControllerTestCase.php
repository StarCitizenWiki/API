<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User;

use App\Http\Controllers\Web\User\UserController;
use App\Models\Account\User\User;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\UserTestCase;

/**
 * Class UserControllerTestCase
 */
class UserControllerTestCase extends UserTestCase
{
    /**
     * Index Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\User\UserController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.users.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('web.users.index');
        }
    }


    /**
     * Edit Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\User\UserController::edit
     */
    public function testEdit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->user)->get(route('web.users.edit', $user->getRouteKey()));
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('web.users.edit');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\UserController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.users.edit', self::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\User\UserController::update
     */
    public function testUpdate()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->followingRedirects()->actingAs($this->user)->patch(
            route('web.users.update', $user->getRouteKey()),
            [
                'api_token' => $user->api_token,
                'no_api_throttle' => 'on',
                'language' => 'de'
            ]
        );

        $user = User::find($user->id);

        $response->assertStatus(static::RESPONSE_STATUSES['update']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('web.users.edit')
                ->assertSee(__('crud.updated', ['type' => __('Benutzer')]))
                ->assertSee($user->username)
                ->assertSee(__('Blockieren'));

            $this->assertTrue($user->settings->isUnthrottled());
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\UserController::update
     * @covers \App\Http\Controllers\Web\User\UserController::block
     */
    public function testBlock()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->followingRedirects()->actingAs($this->user)->patch(
            route('web.users.update', $user->getRouteKey()),
            [
                'block' => true,
            ]
        );

        $user = User::find($user->id);

        $response->assertStatus(static::RESPONSE_STATUSES['block']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('web.users.edit')
                ->assertSee(__('crud.blocked', ['type' => __('Benutzer')]))
                ->assertSee($user->username)
                ->assertSee(__('Freischalten'));

            $this->assertTrue($user->blocked);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\UserController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->user)->patch(
            route('web.users.update', self::MODEL_ID_NOT_EXISTENT),
            []
        );
        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }
}
