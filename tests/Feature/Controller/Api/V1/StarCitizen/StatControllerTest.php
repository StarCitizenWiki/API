<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Models\Api\StarCitizen\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class StatsApiControllerTest
 */
class StatControllerTest extends TestCase
{
    use RefreshDatabase;

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
        $response->assertOk()
            ->assertSee('data')
            ->assertJsonCount(10, 'data')
            ->assertSee('funds')
            ->assertSee('fleet')
            ->assertSee('fans')
            ->assertSee('timestamp')
            ->assertSee('meta');
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
        $response->assertOk()
            ->assertSee('data')
            ->assertSee('funds')
            ->assertSee('fleet')
            ->assertSee('fans')
            ->assertSee('timestamp')
            ->assertJsonCount(4, 'data');
    }

    /**
     * Creates Faked Stats in DB
     */
    protected function setUp()
    {
        parent::setUp();

        factory(Stat::class, 10)->create();
    }
}
