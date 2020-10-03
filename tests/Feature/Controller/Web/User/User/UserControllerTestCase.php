<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\User;

use App\Http\Controllers\Web\User\User\UserController;
use App\Models\Account\User\User;
use Dingo\Api\Dispatcher;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class UserControllerTestCase
 */
class UserControllerTestCase extends UserTestCase
{
    /**
     * Index Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\User\User\UserController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.users.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.users.index');
        }
    }


    /**
     * Edit Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\User\User\UserController::edit
     */
    public function testEdit()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($this->user)->get(route('web.user.users.edit', $user->getRouteKey()));
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.users.edit');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\User\UserController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.users.edit', self::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * @covers \App\Http\Controllers\Web\User\User\UserController::update
     */
    public function testUpdate()
    {
        /** @var \App\Models\Account\User\User $user */
        $user = factory(User::class)->create();

        $response = $this->followingRedirects()->actingAs($this->user)->patch(
            route('web.user.users.update', $user->getRouteKey()),
            [
                'api_token' => $user->api_token,
                'no_api_throttle' => 'on',
            ]
        );

        $user = User::find($user->id);

        $response->assertStatus(static::RESPONSE_STATUSES['update']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.users.edit')
                ->assertSee(__('crud.updated', ['type' => __('Benutzer')]))
                ->assertSee($user->username)
                ->assertSee(__('Blockieren'));

            $this->assertTrue($user->settings->isUnthrottled());
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\User\UserController::update
     * @covers \App\Http\Controllers\Web\User\User\UserController::block
     */
    public function testBlock()
    {
        /** @var \App\Models\Account\User\User $user */
        $user = factory(User::class)->create();

        $response = $this->followingRedirects()->actingAs($this->user)->patch(
            route('web.user.users.update', $user->getRouteKey()),
            [
                'block' => true,
            ]
        );

        $user = User::find($user->id);

        $response->assertStatus(static::RESPONSE_STATUSES['block']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.users.edit')
                ->assertSee(__('crud.blocked', ['type' => __('Benutzer')]))
                ->assertSee($user->username)
                ->assertSee(__('Freischalten'));

            $this->assertTrue($user->blocked);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\User\UserController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->user)->patch(
            route('web.user.users.update', self::MODEL_ID_NOT_EXISTENT),
            []
        );
        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }


    /**
     * @covers \App\Http\Controllers\Web\User\User\UserController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(UserController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(UserController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller, app(Dispatcher::class));
    }
}
