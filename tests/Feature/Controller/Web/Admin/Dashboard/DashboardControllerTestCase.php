<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Web\Admin\Dashboard;

use App\Http\Controllers\Web\Admin\DashboardController;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\Admin\AdminTestCase;

/**
 * Class AbstractBaseAdminControllerTest
 */
class DashboardControllerTestCase extends AdminTestCase
{
    /**
     * @covers \App\Http\Controllers\Web\Admin\DashboardController::show
     *
     * @covers \App\Policies\Web\Admin\DashboardPolicy::view
     */
    public function testShow()
    {
        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->get(route('web.admin.dashboard'));
        $response->assertStatus(static::RESPONSE_STATUSES['show']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.dashboard');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\DashboardController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(DashboardController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth:admin');

        $reflectedClass = new \ReflectionClass(DashboardController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }
}
