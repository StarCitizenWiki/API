<?php

namespace Tests\Feature;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingTransformerException;
use App\Http\Controllers\StarCitizen\StatsAPIController;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class StatsAPIControllerTest extends TestCase
{
    /**
     * Tests Stats from API
     *
     * @covers StatsAPIController::getAll()
     */
    public function testAllApiView()
    {
        $response = $this->get('api/v1/stats/all');
        $response->assertStatus(200);
        $response->assertSee('data');
    }

    /**
     * Tests fans API
     *
     * @covers StatsAPIController::getFans()
     */
    public function testFansApiView()
    {
        $response = $this->get('api/v1/stats/fans');
        $response->assertStatus(200);
        $response->assertSee('fans');
    }

    /**
     * Tests Funds API
     *
     * @covers StatsAPIController::getFunds()
     */
    public function testFundsApiView()
    {
        $response = $this->get('api/v1/stats/funds');
        $response->assertStatus(200);
        $response->assertSee('funds');
    }

    /**
     * Tests fleet api
     *
     * @covers StatsAPIController::getFleet()
     */
    public function testFleetApiView()
    {
        $response = $this->get('api/v1/stats/fleet');
        $response->assertStatus(200);
        $response->assertSee('fleet');
    }
}
