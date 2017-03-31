<?php

namespace Tests\Feature;

use App\Http\Controllers\StarCitizenWiki\ShipsAPIController;
use App\Repositories\StarCitizenWiki\APIv1\Ships\ShipsRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Class ShipsTest
 * @package Tests\Feature
 */
class ShipsAPIControllerTest extends TestCase
{
    /**
     * Get Ship from API
     *
     * @covers ShipsAPIController::getShip()
     */
    public function testApiShipView()
    {
        $response = $this->get('api/v1/ships/300i');
        $response->assertSee('300i');
        $response->assertStatus(200);
    }

    /**
     * Test Search
     *
     * @covers ShipsAPIController::searchShips()
     */
    public function testSearch()
    {
        $response = $this->post('api/v1/ships/search', ['query' => '300i']);
        $response->assertSee('300i');
        $response->assertStatus(200);
    }

    /**
     * @covers ShipsAPIController::getShipList()
     */
    public function testShipsList()
    {
        $response = $this->get('api/v1/ships/list');
        $response->assertSee('300i');
        $response->assertStatus(200);
    }
}
