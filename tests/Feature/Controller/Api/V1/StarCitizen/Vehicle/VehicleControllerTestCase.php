<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Illuminate\Support\Str;
use Tests\Feature\Controller\Api\V1\StarCitizen\StarCitizenTestCase;

/**
 * Base Vehicle Test Case
 */
class VehicleControllerTestCase extends StarCitizenTestCase
{
    /**
     * Vehicle Type that gets created through the Vehicle Factories
     */
    protected const DEFAULT_VEHICLE_TYPE = '';

    /**
     * The Vehicle Count to create on setUp
     */
    protected const VEHICLE_COUNT = 10;


    /**
     * Index Method Tests
     */

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
     * Test Show Specific Vehicle
     *
     * @param string $name The Vehicle Name
     */
    public function testShow(string $name): void
    {
        $this->makeVehicleWithName($name);
        parent::testShow($name);
    }

    /**
     * Creates a Vehicle with specified Name and default translation
     *
     * @param string $name The Name
     *
     * @return Vehicle
     */
    protected function makeVehicleWithName(string $name): Vehicle
    {
        $vehicle = Vehicle::factory()->{static::DEFAULT_VEHICLE_TYPE}()->create(
            [
                'name' => $name,
                'slug' => Str::slug($name),
            ]
        );

        return $vehicle;
    }

    /**
     * Test Show Specific Vehicle with multiple Translations
     *
     * @param string $name The Vehicle Name
     */
    public function testShowMultipleTranslations(string $name): void
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(VehicleTranslation::factory()->german()->make());

        parent::testShowMultipleTranslations($name);
    }

    /**
     * Test Show Specific Vehicle with only german Translation
     *
     * @param string $name The Vehicle Name
     */
    public function testShowLocaleGerman(string $name): void
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(VehicleTranslation::factory()->german()->make());

        parent::testShowLocaleGerman($name);
    }


    /**
     * Search Method Tests
     */

    /**
     * Test Show Specific Vehicle with invalid Locale Code
     *
     * @param string $name The Vehicle Name
     */
    public function testShowLocaleInvalid(string $name): void
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(VehicleTranslation::factory()->german()->make());

        parent::testShowLocaleGerman($name);
    }

    /**
     * Test Search for specific Vehicle
     *
     * @param string $name The Vehicle Name
     */
    public function testSearch(string $name): void
    {
        $this->makeVehicleWithName($name);

        parent::testSearch($name);
    }

    /**
     * Test Search for specific Vehicle with German Translations
     *
     * @param string $name The Vehicle Name
     */
    public function testSearchWithGermanTranslation(string $name): void
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(VehicleTranslation::factory()->german()->make());

        parent::testSearch($name);
    }

    /**
     * Setup Vehicles
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();

        Vehicle::factory()->count(static::VEHICLE_COUNT)->{static::DEFAULT_VEHICLE_TYPE}()->create();
    }
}
