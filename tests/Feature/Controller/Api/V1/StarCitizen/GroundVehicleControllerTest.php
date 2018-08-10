<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Http\Controllers\Api\AbstractApiController;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Tests\Feature\Controller\Api\AbstractApiTestCase as ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController<extended>
 * @covers \App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTransformer<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle<extended>
 * @covers \App\Models\Api\StarCitizen\Manufacturer\Manufacturer<extended>
 * @covers \App\Models\Api\StarCitizen\ProductionNote\ProductionNote<extended>
 * @covers \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType<extended>
 */
class GroundVehicleControllerTest extends ApiTestCase
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
     * Get GroundVehicle from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShow()
    {
        $vehicle = factory(Vehicle::class)->state('ground_vehicle')->create(
            [
                'name' => 'Cyclone',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());

        $response = $this->get('/api/vehicles/ground_vehicles/Cyclone');
        $response->assertOk()->assertSee('Cyclone')->assertJsonStructure(
            [
                'data' => $this->structure,
            ]
        );
    }

    /**
     * Get GroundVehicle from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowMultipleTranslations()
    {
        $vehicle = factory(Vehicle::class)->state('ground_vehicle')->create(
            [
                'name' => 'Cyclone TR',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get('/api/vehicles/ground_vehicles/Cyclone+TR');
        $response->assertOk()->assertSee('Cyclone TR')->assertJsonStructure(
            [
                'data' => $this->structure,
            ]
        )->assertJsonStructure(
            [
                'data' => [
                    'description' => [
                        'en_EN',
                        'de_DE',
                    ],
                ],
            ]
        )->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * Get GroundVehicle from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowLocaleGerman()
    {
        $vehicle = factory(Vehicle::class)->state('ground_vehicle')->create(
            [
                'name' => 'Nova Tank',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get('/api/vehicles/ground_vehicles/Nova+Tank?locale=de_DE');
        $response->assertOk()->assertSee('Nova Tank')->assertJsonStructure(
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
        $vehicle = factory(Vehicle::class)->state('ground_vehicle')->create(
            [
                'name' => 'Ursa Rover',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get('/api/vehicles/ground_vehicles/Ursa+Rover?locale=invalid');
        $response->assertOk()->assertSee('Ursa Rover')->assertJsonStructure(
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::index
     */
    public function testIndexPaginatedDefault()
    {
        $response = $this->get('/api/vehicles/ground_vehicles');
        $response->assertOk()->assertJsonStructure(
            [
                'data' => [
                    $this->structure,
                ],
                'meta' => [],
            ]
        )->assertJsonCount(5, 'data');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::index
     */
    public function testIndexPaginatedCustom()
    {
        $response = $this->get('/api/vehicles/ground_vehicles?limit=1');
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::index
     */
    public function testIndexInvalidLimit()
    {
        $response = $this->get('/api/vehicles/ground_vehicles?limit=-1');
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
        )->assertSee(AbstractApiController::INVALID_LIMIT_STRING);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowNotFound()
    {
        $response = $this->get('/api/vehicles/ground_vehicles/NotExistent');
        $response->assertNotFound()->assertSee(sprintf(AbstractApiController::NOT_FOUND_STRING, 'NotExistent'));
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testSearch()
    {
        $vehicle = factory(Vehicle::class)->state('ground_vehicle')->create(
            [
                'name' => 'Tonk',
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->post(
            '/api/vehicles/ground_vehicles/search',
            [
                'query' => 'Tonk',
            ]
        );

        $response->assertOk()->assertSee('Tonk')->assertJsonStructure(
            [
                'data' => [
                    $this->structure,
                ],
            ]
        )->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testSearchNotFound()
    {
        $response = $this->post(
            '/api/vehicles/ground_vehicles/search',
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

        factory(Vehicle::class, 10)->state('ground_vehicle')->create()->each(
            function ($vehicle) {
                $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
            }
        );
    }
}
