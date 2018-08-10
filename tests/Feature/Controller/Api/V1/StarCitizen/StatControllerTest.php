<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Models\Api\StarCitizen\Stat\Stat;
use Tests\Feature\Controller\Api\AbstractApiTestCase as ApiTestCase;

/**
 * {@inheritdoc}
 */
class StatControllerTest extends ApiTestCase
{
    /**
     * Tests Stats from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Stat\StatController::index
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Stat\StatController::latest
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
