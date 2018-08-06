<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AdminControllerTest
 */
class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showDashboardView
     */
    public function testDashboardViewBureaucrat()
    {
        $admin = factory(Admin::class)->create();
        $group = factory(AdminGroup::class)->states('bureaucrat')->create();
        $admin->groups()->sync($group->id);

        $response = $this->actingAs($admin, 'admin')->get('admin/dashboard');
        $response->assertOk();
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showDashboardView
     */
    public function testDashboardViewSysop()
    {
        $admin = factory(Admin::class)->create();
        $group = factory(AdminGroup::class)->states('sysop')->create();
        $admin->groups()->sync($group->id);

        $response = $this->actingAs($admin, 'admin')->get('admin/dashboard');
        $response->assertOk();
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showDashboardView
     */
    public function testDashboardViewSichter()
    {
        $admin = factory(Admin::class)->create();
        $group = factory(AdminGroup::class)->states('sichter')->create();
        $admin->groups()->sync($group->id);

        $response = $this->actingAs($admin, 'admin')->get('admin/dashboard');
        $response->assertOk();
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showDashboardView
     */
    public function testDashboardViewMitarbeiter()
    {
        $admin = factory(Admin::class)->create();
        $group = factory(AdminGroup::class)->states('mitarbeiter')->create();
        $admin->groups()->sync($group->id);

        $response = $this->actingAs($admin, 'admin')->get('admin/dashboard');
        $response->assertOk();
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showDashboardView
     */
    public function testDashboardViewUser()
    {
        $admin = factory(Admin::class)->create();
        $group = factory(AdminGroup::class)->states('user')->create();
        $admin->groups()->sync($group->id);

        $response = $this->actingAs($admin, 'admin')->get('admin/dashboard');
        $response->assertOk();
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showDashboardView
     */
    public function testDashboardViewBlocked()
    {
        $admin = factory(Admin::class)->create([
            'blocked' => true,
        ]);
        $group = factory(AdminGroup::class)->states('bureaucrat')->create();
        $admin->groups()->sync($group->id);

        $response = $this->actingAs($admin, 'admin')->get('admin/dashboard');
        $response->assertStatus(403);
    }
}
