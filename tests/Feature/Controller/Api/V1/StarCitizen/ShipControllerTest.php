<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Tests\Feature\Controller\Api\AbstractApiTestCase as ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController<extended>
 * @covers \App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer<extended>
 */
class ShipControllerTest extends ApiTestCase
{
    /**
     * Get Ship from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShow()
    {
        $vehicle = factory(Vehicle::class)->create(
            [
                'name' => '300i',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());

        $response = $this->get('/api/vehicles/ships/300i');
        $response->assertOk()->assertSee('300i')->assertJsonStructure(
            [
                'data' => [
                    'description' => [
                        'en_EN',
                    ],
                    'type' => [
                        'en_EN',
                    ],
                ],
            ]
        );
    }

    /**
     * Get Ship from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowMultipleTranslations()
    {
        $vehicle = factory(Vehicle::class)->create(
            [
                'name' => 'Orion',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get('/api/vehicles/ships/Orion');
        $response->assertOk()->assertSee('Orion')->assertJsonStructure(
            [
                'data' => [
                    'description' => [
                        'en_EN',
                        'de_DE',
                    ],
                    'type' => [
                        'en_EN',
                    ],
                ],
            ]
        );
    }

    /**
     * Get Ship from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowLocaleGerman()
    {
        $vehicle = factory(Vehicle::class)->create(
            [
                'name' => '100i',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get('/api/vehicles/ships/100i?locale=de_DE');
        $response->assertOk()->assertSee('100i')->assertJsonStructure(
            [
                'data' => [
                    'description',
                ],
            ]
        )->assertSee('Lorem Ipsum dolor sit amet');
    }

    /**
     * Test Invalid Locale Code
     */
    public function testShowLocaleInvalid()
    {
        $vehicle = factory(Vehicle::class)->create(
            [
                'name' => 'Aurora CL',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get('/api/vehicles/ships/Aurora+CL?locale=invalid');
        $response->assertOk()->assertSee('Aurora CL')->assertJsonStructure(
            [
                'data' => [
                    'description',
                ],
                'meta' => [
                    'errors' => [
                        'locale',
                    ],
                ],
            ]
        )->assertSee('Locale Code invalid is not valid');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::index
     */
    public function testIndexPaginatedDefault()
    {
        $response = $this->get('/api/vehicles/ships');
        $response->assertOk()->assertJsonStructure(
            [
                'data' => [],
                'meta' => [],
            ]
        )->assertJsonCount(5, 'data');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::index
     */
    public function testIndexPaginatedCustom()
    {
        $response = $this->get('/api/vehicles/ships?limit=1');
        $response->assertOk()->assertJsonStructure(
            [
                'data' => [],
                'meta' => [],
            ]
        )->assertJsonCount(1, 'data');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::index
     */
    public function testIndexInvalidLimit()
    {
        $response = $this->get('/api/vehicles/ships?limit=-1');
        $response->assertOk()->assertJsonStructure(
            [
                'data' => [],
                'meta' => [
                    'errors' => [
                        'limit',
                    ],
                ],
            ]
        );
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowNotFound()
    {
        $response = $this->get('/api/vehicles/ships/NotExistent');
        $response->assertNotFound()->assertSee('No Ship found for Query: NotExistent');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testSearch()
    {
        $vehicle = factory(Vehicle::class)->create(
            [
                'name' => 'Hammerhead',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->post(
            '/api/vehicles/ships/search',
            [
                'query' => 'Hammerhead',
            ]
        );

        $response->assertOk()->assertSee('Hammerhead')->assertJsonStructure(
            [
                'data' => [
                    [
                        'description' => [
                            'en_EN',
                            'de_DE',
                        ],
                        'type' => [
                            'en_EN',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testSearchNotFound()
    {
        $response = $this->post(
            '/api/vehicles/ships/search',
            [
                'query' => 'NotExistent',
            ]
        );

        $response->assertNotFound();
    }

    /**
     * Setup Vehicles
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createSystemLanguages();

        factory(Vehicle::class, 10)->create()->each(
            function ($vehicle) {
                $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
            }
        );
    }
}
