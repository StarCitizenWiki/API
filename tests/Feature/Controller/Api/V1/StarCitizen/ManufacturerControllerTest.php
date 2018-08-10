<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Http\Controllers\Api\AbstractApiController;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use Tests\Feature\Controller\Api\AbstractApiTestCase as ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController<extended>
 * @covers \App\Transformers\Api\V1\StarCitizen\Manufacturer\ManufacturerTransformer<extended>
 * @covers \App\Models\Api\StarCitizen\Manufacturer\Manufacturer<extended>
 */
class ManufacturerControllerTest extends ApiTestCase
{
    /**
     * @var array Base Transformer Structure
     */
    private $structure = [
        'code',
        'name',
        'known_for',
        'description',
    ];

    /**
     * Get GroundVehicle from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShow()
    {
        $manufacturer = factory(Manufacturer::class)->create(
            [
                'name_short' => 'RSI',
            ]
        );
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());

        $response = $this->get('/api/manufacturers/RSI');

        $response->assertOk()->assertSee('RSI')->assertJsonStructure(
            [
                'data' => $this->structure,
            ]
        );
    }

    /**
     * Get Manufacturer
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowMultipleTranslations()
    {
        $manufacturer = factory(Manufacturer::class)->create(
            [
                'name_short' => 'ORIG',
            ]
        );
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        $response = $this->get('/api/manufacturers/ORIG');
        $response->assertOk()->assertSee('ORIG')->assertJsonStructure(
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowLocaleGerman()
    {
        $manufacturer = factory(Manufacturer::class)->create(
            [
                'name_short' => 'TMBL',
            ]
        );
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        $response = $this->get('/api/manufacturers/TMBL?locale=de_DE');
        $response->assertOk()->assertSee('TMBL')->assertJsonStructure(
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
        $manufacturer = factory(Manufacturer::class)->create(
            [
                'name_short' => 'DRAK',
            ]
        );
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        $response = $this->get('/api/manufacturers/DRAK?locale=invalid');
        $response->assertOk()->assertSee('DRAK')->assertJsonStructure(
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::index
     */
    public function testIndexPaginatedDefault()
    {
        $response = $this->get('/api/manufacturers');
        $response->assertOk()->assertJsonStructure(
            [
                'data' => [
                    $this->structure,
                ],
                'meta' => [],
            ]
        )->assertJsonCount(10, 'data');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::index
     */
    public function testIndexPaginatedCustom()
    {
        $response = $this->get('/api/manufacturers?limit=1');
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::index
     */
    public function testIndexInvalidLimit()
    {
        $response = $this->get('/api/manufacturers?limit=-1');
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowNotFound()
    {
        $response = $this->get('/api/manufacturers/NotExistent');
        $response->assertNotFound()->assertSee(sprintf(AbstractApiController::NOT_FOUND_STRING, 'NotExistent'));
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testSearch()
    {
        $manufacturer = factory(Manufacturer::class)->create(
            [
                'name_short' => 'AOPOA',
            ]
        );
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        $response = $this->post(
            '/api/manufacturers/search',
            [
                'query' => 'AOPOA',
            ]
        );

        $response->assertOk()->assertSee('AOPOA')->assertJsonStructure(
            [
                'data' => [
                    $this->structure,
                ],
            ]
        )->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testSearchNotFound()
    {
        $response = $this->post(
            '/api/manufacturers/search',
            [
                'query' => 'NotExistent',
            ]
        );

        $response->assertNotFound();
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testRelationInclude()
    {
        /** @var \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer */
        $manufacturer = factory(Manufacturer::class)->create(
            [
                'name_short' => 'BANU',
            ]
        );
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        $manufacturer->ships()->saveMany(
            factory(Vehicle::class, 5)->make()
        );

        $response = $this->get('/api/manufacturers/BANU?with=ships');

        $response->assertOk()->assertJsonStructure(
            [
                'data' => $this->structure,
                'meta' => [],
            ]
        )->assertJsonCount(5, 'data.ships');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testMultipleRelationInclude()
    {
        /** @var \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer */
        $manufacturer = factory(Manufacturer::class)->create(
            [
                'name_short' => 'BANU2',
            ]
        );
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        $manufacturer->ships()->saveMany(
            factory(Vehicle::class, 5)->make()
        );

        $manufacturer->groundVehicles()->saveMany(
            factory(Vehicle::class, 5)->state('ground_vehicle')->make()
        );

        $response = $this->get('/api/manufacturers/BANU2?with=ships,ground_vehicles');

        $response->assertOk()->assertJsonStructure(
            [
                'data' => $this->structure,
                'meta' => [],
            ]
        )->assertJsonCount(5, 'data.ships')->assertJsonCount(5, 'data.ground_vehicles');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::index
     */
    public function testInvalidRelation()
    {
        $response = $this->get('/api/manufacturers?with=invalid');
        $response->assertOk()->assertJsonStructure(
            [
                'data' => [
                    $this->structure,
                ],
                'meta' => [
                    'errors' => [
                        'with',
                    ],
                ],
            ]
        )->assertSee(sprintf(AbstractApiController::INVALID_RELATION_STRING, 'invalid'));
    }

    /**
     * Setup Vehicles
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createSystemLanguages();

        factory(Manufacturer::class, 20)->create()->each(
            function ($manufacturer) {
                $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
            }
        );
    }
}
