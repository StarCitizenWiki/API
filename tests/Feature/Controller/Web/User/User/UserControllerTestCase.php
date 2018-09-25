<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:28
 */

namespace Tests\Feature\Controller\Web\User\User;

use App\Http\Controllers\Web\User\User\UserController;
use App\Models\Account\User\User;
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
        $this->markTestIncomplete();
    }

    /**
     * @covers \App\Http\Controllers\Web\User\User\UserController::update
     */
    public function testUpdateNotFound()
    {
        $this->markTestIncomplete();
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
        $constructor->invoke($controller);
    }
}
