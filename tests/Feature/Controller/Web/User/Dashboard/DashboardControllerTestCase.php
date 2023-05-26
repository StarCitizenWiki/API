<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Dashboard;

use App\Http\Controllers\Web\User\DashboardController;
use Illuminate\Http\Response;
use Octfx\DeepLy\DeepLy;
use Octfx\DeepLy\ResponseBag\UsageBag;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class AbstractBaseAdminControllerTest.
 */
class DashboardControllerTestCase extends UserTestCase
{
    /**
     * @covers \App\Http\Controllers\Web\User\DashboardController::index
     *
     * @covers \App\Policies\Web\User\DashboardPolicy::view
     */
    public function testShow(): void
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

        if (Response::HTTP_OK === $response->status()) {
            $response->assertViewIs('user.dashboard');
        }
    }
}
