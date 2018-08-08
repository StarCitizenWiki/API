<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Admin\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AbstractBaseAdminControllerTest
 */
abstract class AbstractBaseAdminControllerTestCase extends TestCase
{
    use RefreshDatabase;

    protected const RESPONSE_STATUSES = [];

    /**
     * @var \App\Models\Account\Admin\Admin
     */
    protected $admin;

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::showDashboardView
     */
    public function testDashboardView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.dashboard'));
        $response->assertStatus(static::RESPONSE_STATUSES['dashboard']);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createAdminGroups();
    }
}
