<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use Tests\Feature\Controller\Api\V1\StarCitizen\Vehicle\VehicleControllerTestCase;

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
class GroundVehicleControllerTest extends VehicleControllerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const BASE_API_ENDPOINT = '/api/vehicles/ground_vehicles';

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
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShow(string $name = 'Cyclone')
    {
        parent::testShow($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowMultipleTranslations(string $name = 'Cyclone TR')
    {
        parent::testShow($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowLocaleGerman(string $name = 'Nova Tank')
    {
        parent::testShowLocaleGerman($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowLocaleInvalid(string $name = 'Ursa Rover')
    {
        parent::testShowLocaleInvalid($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testShowNotFound()
    {
        parent::testShowNotFound();
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::index
     */
    public function testIndexPaginatedDefault()
    {
        parent::testIndexPaginatedDefault();
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::index
     */
    public function testIndexPaginatedCustom()
    {
        parent::testIndexPaginatedCustom();
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::index
     */
    public function testIndexInvalidLimit()
    {
        parent::testIndexInvalidLimit();
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::search
     */
    public function testSearch(string $name = 'Tonk')
    {
        parent::testSearch($name);
    }

    /**
     * {@inheritdoc}
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::search
     */
    public function testSearchNotFound()
    {
        parent::testSearchNotFound();
    }
}
