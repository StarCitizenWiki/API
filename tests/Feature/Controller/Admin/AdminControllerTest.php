<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\Account\Admin\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AdminControllerTest
 */
class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showRoutesView()
     */
    public function testDashboardView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/dashboard');
        $response->assertStatus(200);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminGroupTableSeeder']);
        $this->admin = factory(Admin::class)->create();
        $this->admin->groups()->sync([4, 5]);
    }
}
