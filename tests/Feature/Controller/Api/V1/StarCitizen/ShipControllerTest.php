<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Http\Controllers\Api\AbstractApiController;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Tests\Feature\Controller\Api\AbstractApiTestCase as ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController<extended>
 * @covers \App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Ship\Ship<extended>
 * @covers \App\Models\Api\StarCitizen\Manufacturer\Manufacturer<extended>
 * @covers \App\Models\Api\StarCitizen\ProductionNote\ProductionNote<extended>
 * @covers \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType<extended>
 */
class ShipControllerTest extends ApiTestCase
{
    /**
     * @var array Base Transformer Structure
     */
    private $structure = [
        'id',
        'chassis_id',
        'sizes' => [
            'length',
            'beam',
            'height',
        ],
        'mass',
        'cargo_capacity',
        'crew' => [
            'min',
            'max',
        ],
        'speed' => [
            'scm',
            'afterburner',
        ],
        'agility' => [
            'pitch',
            'yaw',
            'roll',
            'acceleration' => [
                'x_axis',
                'y_axis',
                'z_axis',
            ],
        ],
        'foci',
        'production_status',
        'production_note',
        'type',
        'description',
        'size',
        'manufacturer' => [
            'code',
            'name',
        ],
    ];

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
                'data' => $this->structure,
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
                'data' => $this->structure,
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
                'data' => $this->structure,
            ]
        )->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
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
                'data' => $this->structure,
                'meta' => [
                    'errors' => [
                        'locale',
                    ],
                ],
            ]
        )->assertSee(sprintf(AbstractApiController::INVALID_LOCALE_STRING, 'invalid'));
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
                'data' => [
                    $this->structure,
                ],
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
                'data' => [
                    $this->structure,
                ],
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
        $response->assertNotFound()->assertSee(sprintf(AbstractApiController::NOT_FOUND_STRING, 'NotExistent'));
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
                    $this->structure,
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
