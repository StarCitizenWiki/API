<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use Tests\TestCase;

/**
 * Class ShipsTest
 */
class ShipControllerTest extends TestCase
{
    /**
     * Get Ship from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show()
     * @covers \App\Http\Middleware\ThrottleApi
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     *
     * @TODO   Model Factories?
     */
    public function testApiShipView()
    {
        $response = $this->get('/api/vehicles/ships/300i');
        //$response->assertSee('300i');
        $response->assertStatus(404);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::index()
     * @covers \App\Http\Middleware\ThrottleApi
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     *
     * @TODO   Model Factories?
     */
    public function testShipsList()
    {
        $response = $this->get('/api/vehicles/ships');
        $response->assertSee('meta');
        $response->assertStatus(200);
    }
}
