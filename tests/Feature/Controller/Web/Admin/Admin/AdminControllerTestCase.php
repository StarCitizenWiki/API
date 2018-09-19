<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Web\Admin\Admin;

use App\Models\Account\Admin\Admin;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\Admin\AdminTestCase;

/**
 * Class AbstractBaseAdminControllerTest
 */
class AdminControllerTestCase extends AdminTestCase
{
    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showDashboardView
     */
    public function testDashboardView()
    {
        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->get(route('web.admin.dashboard'));
        $response->assertStatus(static::RESPONSE_STATUSES['dashboard']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.dashboard');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::index
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
     * @covers \App\Http\Controllers\Web\Admin\AdminController::edit
     */
    public function testEdit()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.admins.edit', $this->admin));
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.admins.edit')
                ->assertSee($this->admin->username)
                ->assertSee($this->admin->provider)
                ->assertSee('Blockieren');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::update
     */
    public function testUpdate()
    {
        $localAdmin = factory(Admin::class)->create();

        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->patch(
            route('web.admin.admins.update', $localAdmin)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['update']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.admins.edit')
                ->assertSee($localAdmin->username)
                ->assertSee($localAdmin->provider)
                ->assertSee('Blockieren');
            $this->assertEquals(true, Admin::where('id', $localAdmin->id)->first()->blocked);
        }
    }
}
