<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 18:05
 */

namespace Tests\Feature\Controller\Api\V1\StarCitizen\Vehicle;

use App\Http\Controllers\Api\AbstractApiController;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Tests\Feature\Controller\Api\ApiTestCase;

/**
 * Base Vehicle Test Case
 */
class VehicleControllerTestCase extends ApiTestCase
{
    /**
     * Show Api Endpoint without Trailing Slash
     */
    protected const BASE_API_ENDPOINT = '';

    /**
     * Vehicle Type that gets created through the Vehicle Factories
     */
    protected const DEFAULT_VEHICLE_TYPE = '';

    /**
     * Name to use for 'Not Found' Tests
     */
    protected const NOT_EXISTENT_NAME = 'NotExistent';

    /**
     * The Vehicle Count to create on setUp
     */
    protected const VEHICLE_COUNT = 10;

    /**
     * @var array Base Transformer Structure
     */
    protected $structure = [];


    /**
     * Show Method Tests
     */

    /**
     * Test Show Specific Vehicle
     *
     * @param string $name The Vehicle Name
     */
    public function testShow(string $name)
    {
        $vehicle = $this->makeVehicleWithName($name);

        $response = $this->get(
            sprintf(
                '%s/%s%s',
                static::BASE_API_ENDPOINT,
                urlencode($name),
                ''

            )
        );
        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta',
                ]
            );
    }

    /**
     * Test Show Specific Vehicle with multiple Translations
     *
     * @param string $name The Vehicle Name
     */
    public function testShowMultipleTranslations(string $name)
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get(
            sprintf(
                '%s/%s%s',
                static::BASE_API_ENDPOINT,
                urlencode($name),
                ''
            )
        );
        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta',
                ]
            )
            ->assertJsonStructure(
                [
                    'data' => [
                        'description' => [
                            'en_EN',
                            'de_DE',
                        ],
                    ],
                ]
            )
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * Test Show Specific Vehicle with only german Translation
     *
     * @param string $name The Vehicle Name
     */
    public function testShowLocaleGerman(string $name)
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get(
            sprintf(
                '%s/%s%s',
                static::BASE_API_ENDPOINT,
                urlencode($name),
                '?locale=de_DE'
            )
        );

        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta',
                ]
            )
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * Test Show Specific Vehicle with invalid Locale Code
     *
     * @param string $name The Vehicle Name
     */
    public function testShowLocaleInvalid(string $name)
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->get(
            sprintf(
                '%s/%s%s',
                static::BASE_API_ENDPOINT,
                urlencode($name),
                '?locale=invalid'
            )
        );

        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta' => [
                        'errors' => [
                            'locale',
                        ],
                    ],
                ]
            )
            ->assertSee(
                sprintf(
                    AbstractApiController::INVALID_LOCALE_STRING,
                    'invalid'
                )
            );
    }

    /**
     * Test Show Specific Vehicle which does not exist
     */
    public function testShowNotFound()
    {
        $response = $this->get(
            sprintf(
                '%s/%s%s',
                static::BASE_API_ENDPOINT,
                static::NOT_EXISTENT_NAME,
                ''
            )
        );

        $response->assertNotFound()
            ->assertSee(
                sprintf(
                    AbstractApiController::NOT_FOUND_STRING,
                    static::NOT_EXISTENT_NAME
                )
            );
    }



    /**
     * Index Method Tests
     */

    /**
     * Test Index with default Pagination
     */
    public function testIndexPaginatedDefault()
    {
        $response = $this->get(static::BASE_API_ENDPOINT);

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta',
                ]
            )
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test Index with custom Pagination (limit)
     */
    public function testIndexPaginatedCustom()
    {
        $response = $this->get(
            sprintf(
                '%s%s',
                static::BASE_API_ENDPOINT,
                '?limit=1'
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta',
                ]
            )
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test Index with invalid Pagination (limit)
     */
    public function testIndexInvalidLimit()
    {
        $response = $this->get(
            sprintf(
                '%s%s',
                static::BASE_API_ENDPOINT,
                '?limit=-1'
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
                            'limit',
                        ],
                    ],
                ]
            )
            ->assertSee(AbstractApiController::INVALID_LIMIT_STRING);
    }



    /**
     * Search Method Tests
     */

    /**
     * Test Search for specific Vehicle with German Translations
     *
     * @param string $name The Vehicle Name
     */
    public function testSearch(string $name)
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        $response = $this->post(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                'search'
            ),
            [
                'query' => $name,
            ]
        );

        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta',
                ]
            )
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'description' => [
                                'en_EN',
                                'de_DE',
                            ],
                        ],
                    ],
                ]
            )
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * Test Search for not existent vehicle
     */
    public function testSearchNotFound()
    {
        $response = $this->post(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                'search'
            ),
            [
                'query' => static::NOT_EXISTENT_NAME,
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

        factory(Vehicle::class, static::VEHICLE_COUNT)->state(static::DEFAULT_VEHICLE_TYPE)->create()->each(
            function (Vehicle $vehicle) {
                $vehicle->translations()->save(factory(VehicleTranslation::class)->make());
            }
        );
    }

    /**
     * Creates a Vehicle with specified Name and default translation
     *
     * @param string $name The Name
     *
     * @return mixed
     */
    private function makeVehicleWithName(string $name)
    {
        $vehicle = factory(Vehicle::class)->state(static::DEFAULT_VEHICLE_TYPE)->create(
            [
                'name' => $name,
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());

        return $vehicle;
    }
}
