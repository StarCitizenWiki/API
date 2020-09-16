<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use Tests\Feature\Controller\Api\V1\StarCitizen\Vehicle\VehicleControllerTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController<extended>
 *
 * @covers \App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer<extended>
 *
 * @covers \App\Models\Api\StarCitizen\Vehicle\Ship\Ship<extended>
 * @covers \App\Models\Api\StarCitizen\Manufacturer\Manufacturer<extended>
 * @covers \App\Models\Api\StarCitizen\ProductionNote\ProductionNote<extended>
 * @covers \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Focus\Focus<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Size\Size<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Type\Type<extended>
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
    public function testIndexAll(int $allCount = 0)
    {
        parent::testIndexAll(Ship::count());
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedCustom(int $limit = 2)
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShow(string $name = '300i')
    {
        parent::testShow($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowMultipleTranslations(string $name = 'Orion')
    {
        parent::testShowMultipleTranslations($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowLocaleGerman(string $name = '100i')
    {
        parent::testShowLocaleGerman($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testShowLocaleInvalid(string $name = 'Aurora CL')
    {
        parent::testShowLocaleInvalid($name);
    }


    /**
     * Search Method Tests
     */

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::search
     */
    public function testSearch(string $name = 'Hammerhead')
    {
        parent::testSearch($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::search
     */
    public function testSearchWithGermanTranslation(string $name = 'Merchantman')
    {
        parent::testSearch($name);
    }
}
