<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Admin\Admin;

use Tests\Feature\Controller\Admin\AdminTestCase;

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
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.dashboard'));
        $response->assertStatus(static::RESPONSE_STATUSES['dashboard']);
    }
}
