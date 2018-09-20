<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Web\Admin\Admin;

use App\Http\Controllers\Web\Admin\Admin\AdminController;
use App\Models\Account\Admin\Admin;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\Admin\AdminTestCase;

/**
 * Class AbstractBaseAdminControllerTest
 */
class AdminControllerTestCase extends AdminTestCase
{
    /**
     * @covers \App\Http\Controllers\Web\Admin\Admin\AdminController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.admins.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.admins.index')->assertSee($this->admin->username);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Admin\AdminController::edit
     */
    public function testEdit()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.admins.edit', $this->admin));
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.admins.edit')
                ->assertSee($this->admin->username)
                ->assertSee($this->admin->provider)
                ->assertSee(__('Blockieren'));
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Admin\AdminController::update
     */
    public function testUpdateBlock()
    {
        $localAdmin = factory(Admin::class)->create();

        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->patch(
            route('web.admin.admins.update', $localAdmin),
            [
                'block' => true,
            ]
        );
        $response->assertStatus(static::RESPONSE_STATUSES['update']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.admins.edit')
                ->assertSee($localAdmin->username)
                ->assertSee($localAdmin->provider)
                ->assertSee(__('Freischalten'));
            $this->assertEquals(true, Admin::where('id', $localAdmin->id)->first()->blocked);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Admin\AdminController::update
     */
    public function testUpdateRestore()
    {
        $localAdmin = factory(Admin::class)->state('blocked')->create();

        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->patch(
            route('web.admin.admins.update', $localAdmin),
            [
                'restore' => true,
            ]
        );
        $response->assertStatus(static::RESPONSE_STATUSES['update']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.admins.edit')
                ->assertSee($localAdmin->username)
                ->assertSee($localAdmin->provider)
                ->assertSee(__('Blockieren'));
            $this->assertEquals(false, Admin::where('id', $localAdmin->id)->first()->blocked);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Admin\AdminController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(AdminController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth:admin');

        $reflectedClass = new \ReflectionClass(AdminController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }
}
