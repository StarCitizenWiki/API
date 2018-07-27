<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api;

use Tests\TestCase;

/**
 * Class StatsApiControllerTest
 */
class StatControllerTest extends TestCase
{
    /**
     * Tests Stats from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Stat\StatController::index()
     * @covers \App\Http\Middleware\ThrottleApi
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     */
    public function testAllApiView()
    {
        $response = $this->get('/api/stats');
        $response->assertStatus(200);
        $response->assertSee('data');
        $response->assertSee('meta');
    }

    /**
     * Tests Stats from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Stat\StatController::latest()
     * @covers \App\Http\Middleware\ThrottleApi
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     */
    public function testLatestApiView()
    {
        $response = $this->get('/api/stats/latest');
        $response->assertStatus(200);
        $response->assertSee('funds');
    }

}
