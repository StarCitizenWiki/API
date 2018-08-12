<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Http\Controllers\Api\AbstractApiController;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController<extended>
 *
 * @covers \App\Transformers\Api\V1\StarCitizen\Manufacturer\ManufacturerTransformer<extended>
 *
 * @covers \App\Models\Api\StarCitizen\Manufacturer\Manufacturer<extended>
 */
class ManufacturerControllerTest extends StarCitizenTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const MODEL_DEFAULT_PAGINATION_COUNT = 10;

    /**
     * {@inheritdoc}
     */
    protected const BASE_API_ENDPOINT = '/api/manufacturers';

    /**
     * {@inheritdoc}
     */
    protected $structure = [
        'code',
        'name',
        'known_for',
        'description',
    ];


    /**
     * Index Method Tests
     */

    /**
     * {@inheritdoc}
     */
    public function testIndexAll(int $allCount = 0)
    {
        parent::testIndexAll(Manufacturer::count());
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedCustom(int $limit = 5)
    {
        parent::testIndexPaginatedCustom($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexInvalidLimit(int $limit = -1)
    {
        parent::testIndexInvalidLimit($limit);
    }


    /**
     * Show Method Tests
     */

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShow(string $name = 'RSI')
    {
        $this->makeManufacturerWithName($name);

        parent::testShow($name);
    }


    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowMultipleTranslations(string $name = 'ORIG')
    {
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        parent::testShowMultipleTranslations($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowLocaleGerman(string $name = 'TMBL')
    {
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        parent::testShowLocaleGerman($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowLocaleInvalid(string $name = 'DRAK')
    {
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        parent::testShowLocaleInvalid($name);
    }


    /**
     * Search Method Tests
     */

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::search
     */
    public function testSearch(string $name = 'AOPOA')
    {
        $this->makeManufacturerWithName($name);

        parent::testSearch($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::search
     */
    public function testSearchWithGermanTranslation(string $name = 'ANVL')
    {
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        parent::testSearchWithGermanTranslation($name);
    }


    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testRelationInclude()
    {
        $manufacturer = $this->makeManufacturerWithName('BANU');
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        $manufacturer->ships()->saveMany(
            factory(Vehicle::class, 5)->make()
        );

        $response = $this->get(
            sprintf(
                '%s/%s?with=%s',
                static::BASE_API_ENDPOINT,
                'BANU',
                'ships'
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
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
        $manufacturer = $this->makeManufacturerWithName('VANDUUL');
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->state('german')->make());

        $manufacturer->ships()->saveMany(
            factory(Vehicle::class, 5)->make()
        );

        $manufacturer->groundVehicles()->saveMany(
            factory(Vehicle::class, 5)->state('ground_vehicle')->make()
        );

        $response = $this->get(
            sprintf(
                '%s/%s?with=%s',
                static::BASE_API_ENDPOINT,
                'VANDUUL',
                'ships,ground_vehicles'
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta' => [],
                ]
            )
            ->assertJsonCount(5, 'data.ships')
            ->assertJsonCount(5, 'data.ground_vehicles');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::index
     */
    public function testInvalidRelation()
    {
        $response = $this->get(
            sprintf(
                '%s?with=%s',
                static::BASE_API_ENDPOINT,
                'invalid'
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
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
            )
            ->assertSee(sprintf(AbstractApiController::INVALID_RELATION_STRING, 'invalid'));
    }


    /**
     * Setup Vehicles
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createSystemLanguages();

        factory(Manufacturer::class, 20)->create()->each(
            function (Manufacturer $manufacturer) {
                $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
            }
        );
    }

    /**
     * Creates a Manufacturer with specified Name and default translation
     *
     * @param string $name The Name
     *
     * @return \App\Models\Api\StarCitizen\Manufacturer\Manufacturer
     */
    private function makeManufacturerWithName(string $name)
    {
        $manufacturer = factory(Manufacturer::class)->create(
            [
                'name' => $name,
            ]
        );
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());

        return $manufacturer;
    }
}
