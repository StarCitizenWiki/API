<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Web\User\Dashboard;

use App\Http\Controllers\Web\User\DashboardController;
use Illuminate\Http\Response;
use StarCitizenWiki\DeepLy\DeepLy;
use StarCitizenWiki\DeepLy\ResponseBag\UsageBag;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class AbstractBaseAdminControllerTest
 */
class DashboardControllerTestCase extends UserTestCase
{
    /**
     * @covers \App\Http\Controllers\Web\User\DashboardController::index
     *
     * @covers \App\Policies\Web\User\DashboardPolicy::view
     */
    public function testShow()
    {
        $mock = \Mockery::mock(DeepLy::class);
        $mock->shouldReceive('getUsage')->zeroOrMoreTimes()->andReturn(
            new UsageBag(
                json_decode('{"character_count": 180000, "character_limit": 1250000}')
            )
        );
        $this->app->instance('deeply', $mock);

        $response = $this->actingAs($this->user)->followingRedirects()->get(route('web.user.dashboard'));
        $response->assertStatus(static::RESPONSE_STATUSES['show']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.dashboard');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\DashboardController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(DashboardController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(DashboardController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }
}
