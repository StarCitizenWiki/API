<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Component\Component;
use App\Models\StarCitizen\Vehicle\Ship\Ship;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController<extended>
 *
 * @covers \App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer<extended>
 *
 * @covers \App\Models\StarCitizen\Vehicle\Ship\Ship<extended>
 * @covers \App\Models\StarCitizen\Manufacturer\Manufacturer<extended>
 * @covers \App\Models\StarCitizen\ProductionNote\ProductionNote<extended>
 * @covers \App\Models\StarCitizen\ProductionStatus\ProductionStatus<extended>
 * @covers \App\Models\StarCitizen\Vehicle\Focus\Focus<extended>
 * @covers \App\Models\StarCitizen\Vehicle\Size\Size<extended>
 * @covers \App\Models\StarCitizen\Vehicle\Type\Type<extended>
 */
class ShipControllerTest extends VehicleControllerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const BASE_API_ENDPOINT = '/api/ships';

    /**
     * {@inheritdoc}
     */
    protected const DEFAULT_VEHICLE_TYPE = 'ship';

    /**
     * @var array Base Transformer Structure
     */
    protected $structure = [
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
     * Index Method Tests
     */

    /**
     * {@inheritdoc}
     */
    public function testIndexAll(int $allCount = 0): void
    {
        parent::testIndexAll(Ship::count());
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedCustom(int $limit = 2): void
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShow(string $name = '300i'): void
    {
        parent::testShow($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowMultipleTranslations(string $name = 'Orion'): void
    {
        parent::testShowMultipleTranslations($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowLocaleGerman(string $name = '100i'): void
    {
        parent::testShowLocaleGerman($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowLocaleInvalid(string $name = 'Aurora CL'): void
    {
        parent::testShowLocaleInvalid($name);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     * @covers \App\Transformers\Api\V1\StarCitizen\Vehicle\ComponentTransformer
     * @covers \App\Models\StarCitizen\Vehicle\Vehicle\Vehicle::components
     */
    public function testShowIncludeComponents(): void
    {
        $vehicle = $this->makeVehicleWithName('UberVehicle');
        $vehicle->components()->saveMany(
            Component::factory()->count(20)->make(),
            [
                'size' => 1,
                'details' => '',
                'quantity' => 1,
                'mounts' => 2,
            ]
        );

        $response = $this->get(
            sprintf(
                '%s/%s?include=components',
                static::BASE_API_ENDPOINT,
                urlencode('UberVehicle'),
            )
        );

        $structure = $this->structure;
        $structure['components'] = [
            'data' => [
                '*' => [
                    'type',
                    'name',
                    'mounts',
                    'component_size',
                    'category',
                    'size',
                    'details',
                    'quantity',
                    'manufacturer',
                    'component_class',
                ],
            ],
        ];

        $response->assertOk()
            ->assertSee('UberVehicle')
            ->assertJsonStructure(
                [
                    'data' => $structure,
                    'meta',
                ]
            )
            ->assertJsonCount($vehicle->components->count(), 'data.components.data');
    }


    /**
     * Search Method Tests
     */

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::search
     * @covers \App\Http\Requests\StarCitizen\Vehicle\StarsystemRequest
     */
    public function testSearch(string $name = 'Hammerhead'): void
    {
        parent::testSearch($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::search
     * @covers \App\Http\Requests\StarCitizen\Vehicle\StarsystemRequest
     */
    public function testSearchWithGermanTranslation(string $name = 'Merchantman'): void
    {
        parent::testSearch($name);
    }
}
