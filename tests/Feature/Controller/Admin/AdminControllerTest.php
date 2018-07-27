<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\Account\Admin\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class AdminControllerTest
 * @package Tests\Feature\Controller
 */
class AdminControllerTest extends TestCase
{
    use DatabaseTransactions;

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
        $this->admin = Admin::find(1);
    }
}
