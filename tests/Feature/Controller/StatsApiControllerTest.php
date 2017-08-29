<?php declare(strict_types = 1);

namespace Tests\Feature\Controller;

use Tests\TestCase;

/**
 * Class StatsApiControllerTest
 * @package Tests\Feature\Controller
 */
class StatsApiControllerTest extends TestCase
{
    /**
     * Tests Stats from Interfaces
     *
     * @covers \App\Http\Controllers\StarCitizen\StatsApiController::getAll()
     * @covers \App\Http\Middleware\ThrottleApi
     * @covers \App\Http\Middleware\AddApiHeaders
     * @covers \App\Http\Middleware\PiwikTracking
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     * @covers \App\Transformers\StarCitizen\Stats\StatsTransformer
     * @covers \App\Traits\RestTrait
     * @covers \App\Traits\TransformsDataTrait
     * @covers \App\Traits\RestExceptionHandlerTrait
     * @covers \App\Traits\FiltersDataTrait
     */
    public function testAllApiView()
    {
        $response = $this->get('api/v1/stats/all');
        $response->assertStatus(200);
        $response->assertSee('data');
    }

    /**
     * Tests fans Interfaces
     *
     * @covers \App\Http\Controllers\StarCitizen\StatsApiController::getFans()
     * @covers \App\Http\Middleware\ThrottleApi
     * @covers \App\Http\Middleware\AddApiHeaders
     * @covers \App\Http\Middleware\PiwikTracking
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     * @covers \App\Transformers\StarCitizen\Stats\FansTransformer
     * @covers \App\Traits\RestTrait
     * @covers \App\Traits\TransformsDataTrait
     * @covers \App\Traits\RestExceptionHandlerTrait
     * @covers \App\Traits\FiltersDataTrait
     */
    public function testFansApiView()
    {
        $response = $this->get('api/v1/stats/fans');
        $response->assertStatus(200);
        $response->assertSee('fans');
    }

    /**
     * Tests Funds Interfaces
     *
     * @covers \App\Http\Controllers\StarCitizen\StatsApiController::getFunds()
     * @covers \App\Http\Middleware\ThrottleApi
     * @covers \App\Http\Middleware\AddApiHeaders
     * @covers \App\Http\Middleware\PiwikTracking
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     * @covers \App\Transformers\StarCitizen\Stats\FundsTransformer
     * @covers \App\Traits\RestTrait
     * @covers \App\Traits\TransformsDataTrait
     * @covers \App\Traits\RestExceptionHandlerTrait
     * @covers \App\Traits\FiltersDataTrait
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
     * @covers \App\Http\Controllers\StarCitizen\StatsApiController::getFleet()
     * @covers \App\Http\Middleware\ThrottleApi
     * @covers \App\Http\Middleware\AddApiHeaders
     * @covers \App\Http\Middleware\PiwikTracking
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     * @covers \App\Transformers\StarCitizen\Stats\FleetTransformer
     * @covers \App\Traits\RestTrait
     * @covers \App\Traits\TransformsDataTrait
     * @covers \App\Traits\RestExceptionHandlerTrait
     * @covers \App\Traits\FiltersDataTrait
     */
    public function testFleetApiView()
    {
        $response = $this->get('api/v1/stats/fleet');
        $response->assertStatus(200);
        $response->assertSee('fleet');
    }
}
