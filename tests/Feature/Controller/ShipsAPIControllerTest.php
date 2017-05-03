<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;

/**
 * Class ShipsTest
 * @package Tests\Feature
 */
class ShipsAPIControllerTest extends TestCase
{
    /**
     * Get Ship from Interfaces
     *
     * @covers \App\Http\Controllers\StarCitizenWiki\ShipsAPIController::getShip()
     * @covers \App\Http\Middleware\ThrottleAPI
     * @covers \App\Http\Middleware\AddAPIHeaders
     * @covers \App\Http\Middleware\PiwikTracking
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     * @covers \App\Transformers\StarCitizenWiki\Ships\ShipsTransformer
     * @covers \App\Traits\RestTrait
     * @covers \App\Traits\TransformesDataTrait
     * @covers \App\Traits\RestExceptionHandlerTrait
     * @covers \App\Traits\FiltersDataTrait
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
     * @covers \App\Http\Controllers\StarCitizenWiki\ShipsAPIController::searchShips()
     * @covers \App\Http\Middleware\ThrottleAPI
     * @covers \App\Http\Middleware\AddAPIHeaders
     * @covers \App\Http\Middleware\PiwikTracking
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     * @covers \App\Transformers\StarCitizenWiki\Ships\ShipsSearchTransformer
     * @covers \App\Traits\RestTrait
     * @covers \App\Traits\TransformesDataTrait
     * @covers \App\Traits\RestExceptionHandlerTrait
     * @covers \App\Traits\FiltersDataTrait
     */
    public function testSearch()
    {
        $response = $this->post('api/v1/ships/search', ['query' => '300i']);
        $response->assertSee('300i');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\StarCitizenWiki\ShipsAPIController::getShipList()
     * @covers \App\Http\Middleware\ThrottleAPI
     * @covers \App\Http\Middleware\AddAPIHeaders
     * @covers \App\Http\Middleware\PiwikTracking
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     * @covers \App\Transformers\StarCitizenWiki\Ships\ShipsListTransformer
     * @covers \App\Traits\RestTrait
     * @covers \App\Traits\TransformesDataTrait
     * @covers \App\Traits\RestExceptionHandlerTrait
     * @covers \App\Traits\FiltersDataTrait
     */
    public function testShipsList()
    {
        $response = $this->get('api/v1/ships/list');
        $response->assertSee('300i');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\StarCitizenWiki\ShipsAPIController::getShipList()
     * @covers \App\Http\Middleware\ThrottleAPI
     * @covers \App\Http\Middleware\AddAPIHeaders
     * @covers \App\Http\Middleware\PiwikTracking
     * @covers \App\Http\Middleware\UpdateTokenTimestamp
     * @covers \App\Transformers\StarCitizenWiki\Ships\ShipsListTransformer
     * @covers \App\Traits\RestTrait
     * @covers \App\Traits\TransformesDataTrait
     * @covers \App\Traits\RestExceptionHandlerTrait
     * @covers \App\Traits\FiltersDataTrait
     */
    public function testShipsListFilter()
    {
        $response = $this->get('api/v1/ships/list?fields=wiki_url');
        $response->assertSee('300i');
        $response->assertStatus(200);
    }
}
