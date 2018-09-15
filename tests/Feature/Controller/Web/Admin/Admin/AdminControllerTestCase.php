<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Web\Admin\Admin;

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
}
