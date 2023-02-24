<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\StarCitizen\Manufacturer\ManufacturerTranslation;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use Illuminate\Support\Str;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController<extended>
 *
 * @covers \App\Transformers\Api\V1\StarCitizen\Manufacturer\ArticleTransformer<extended>
 * @covers \App\Transformers\Api\V1\StarCitizen\Vehicle\VehicleLinkTransformer
 *
 * @covers \App\Models\StarCitizen\Manufacturer\Manufacturer<extended>
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
    public function testIndexAll(int $allCount = 0): void
    {
        parent::testIndexAll(Manufacturer::count());
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedCustom(int $limit = 5): void
    {
        parent::testIndexPaginatedCustom($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexInvalidLimit(int $limit = -1): void
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
    public function testShow(string $name = 'RSI'): void
    {
        $this->makeManufacturerWithName($name);

        parent::testShow($name);
    }

    /**
     * Creates a Manufacturer with specified Name and default translation
     *
     * @param string $name The Name
     *
     * @return Manufacturer
     */
    private function makeManufacturerWithName(string $name): Manufacturer
    {
        $manufacturer = Manufacturer::factory()->create(
            [
                'name' => $name,
            ]
        );

        return $manufacturer;
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowMultipleTranslations(string $name = 'ORIG'): void
    {
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(ManufacturerTranslation::factory()->german()->make());

        parent::testShowMultipleTranslations($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowLocaleGerman(string $name = 'TMBL'): void
    {
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(ManufacturerTranslation::factory()->german()->make());

        parent::testShowLocaleGerman($name);
    }


    /**
     * Search Method Tests
     */

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testShowLocaleInvalid(string $name = 'DRAK'): void
    {
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(ManufacturerTranslation::factory()->german()->make());

        parent::testShowLocaleInvalid($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::search
     * @covers \App\Http\Requests\StarCitizen\Manufacturer\ManufacturerSearchRequest
     */
    public function testSearch(string $name = 'AOPOA'): void
    {
        $this->makeManufacturerWithName($name);

        parent::testSearch($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::search
     * @covers \App\Http\Requests\StarCitizen\Manufacturer\ManufacturerSearchRequest
     */
    public function testSearchWithGermanTranslation(string $name = 'ANVL'): void
    {
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(ManufacturerTranslation::factory()->german()->make());

        parent::testSearchWithGermanTranslation($name);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testRelationInclude(): void
    {
        $name = Str::random(6);
        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(ManufacturerTranslation::factory()->german()->make());

        $manufacturer->ships()->saveMany(
            Vehicle::factory()->count(5)->make()
        );

        $response = $this->get(
            sprintf(
                '%s/%s?include=%s',
                static::BASE_API_ENDPOINT,
                $name,
                'ships'
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta' => [],
                ]
            )
            ->assertJsonCount($manufacturer->ships()->count(), 'data.ships.data')
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testMultipleRelationInclude(): void
    {
        $name = Str::random(5);

        $manufacturer = $this->makeManufacturerWithName($name);
        $manufacturer->translations()->save(ManufacturerTranslation::factory()->german()->make());

        $manufacturer->ships()->saveMany(
            Vehicle::factory()->count(5)->ship()->make()
        );

        $manufacturer->vehicles()->saveMany(
            Vehicle::factory()->count(5)->groundVehicle()->make()
        );

        $response = $this->get(
            sprintf(
                '%s/%s?include=%s',
                static::BASE_API_ENDPOINT,
                urlencode($name),
                'ships,vehicles'
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta' => [],
                ]
            )
            ->assertJsonCount($manufacturer->ships()->count(), 'data.ships.data')
            ->assertJsonCount($manufacturer->vehicles()->count(), 'data.vehicles.data')
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::index
     */
    public function testInvalidRelation(): void
    {
        $response = $this->get(
            sprintf(
                '%s?include=%s',
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
                ]
            )
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }

    /**
     * Setup Vehicles
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();

        Manufacturer::factory()->count(20)->create();
    }
}
