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
        $group = AdminGroup::where('name', 'bureaucrat')->first();
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
        $group = AdminGroup::where('name', 'sysop')->first();
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
        $group = AdminGroup::where('name', 'sichter')->first();
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
        $group = AdminGroup::where('name', 'mitarbeiter')->first();
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
        $group = AdminGroup::where('name', 'user')->first();
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

        $admin->groups()->sync(AdminGroup::where('name', 'bureaucrat')->first()->id);

        $response = $this->actingAs($admin, 'admin')->get('admin/dashboard');
        $response->assertStatus(403);
    }

    protected function setUp()
    {
        parent::setUp();
        if (AdminGroup::count() !== 5) {
            $this->createAdminGroups();
        }
    }
}
