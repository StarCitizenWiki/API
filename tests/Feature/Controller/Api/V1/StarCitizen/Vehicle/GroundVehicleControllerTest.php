<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController<extended>
 *
 * @covers \App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTransformer<extended>
 *
 * @covers \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle<extended>
 * @covers \App\Models\Api\StarCitizen\Manufacturer\Manufacturer<extended>
 * @covers \App\Models\Api\StarCitizen\ProductionNote\ProductionNote<extended>
 * @covers \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Focus\Focus<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Size\Size<extended>
 * @covers \App\Models\Api\StarCitizen\Vehicle\Type\Type<extended>
 */
class GroundVehicleControllerTest extends VehicleControllerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const BASE_API_ENDPOINT = '/api/vehicles';

    /**
     * {@inheritdoc}
     */
    protected const DEFAULT_VEHICLE_TYPE = 'ground_vehicle';

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
        parent::testIndexAll(GroundVehicle::count());
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
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShow(string $name = 'Cyclone'): void
    {
        parent::testShow($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowMultipleTranslations(string $name = 'Cyclone TR'): void
    {
        parent::testShowMultipleTranslations($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowLocaleGerman(string $name = 'Nova Tank'): void
    {
        parent::testShowLocaleGerman($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowLocaleInvalid(string $name = 'Ursa Rover'): void
    {
        parent::testShowLocaleInvalid($name);
    }


    /**
     * Search Method Tests
     */

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::search
     * @covers \App\Http\Requests\StarCitizen\Vehicle\GroundVehicleSearchRequest
     */
    public function testSearch(string $name = 'Tonk'): void
    {
        parent::testSearch($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::search
     * @covers \App\Http\Requests\StarCitizen\Vehicle\GroundVehicleSearchRequest
     */
    public function testSearchWithGermanTranslation(string $name = 'Tonk2'): void
    {
        parent::testSearch($name);
    }
}
