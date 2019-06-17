<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 18:05
 */

namespace Tests\Feature\Controller\Api\V1\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
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
     * Test Show Specific Vehicle
     *
     * @param string $name The Vehicle Name
     */
    public function testShow(string $name)
    {
        $this->makeVehicleWithName($name);
        parent::testShow($name);
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

        parent::testShowMultipleTranslations($name);
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

        parent::testShowLocaleGerman($name);
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

        parent::testShowLocaleGerman($name);
    }


    /**
     * Search Method Tests
     */

    /**
     * Test Search for specific Vehicle
     *
     * @param string $name The Vehicle Name
     */
    public function testSearch(string $name)
    {
        $this->makeVehicleWithName($name);

        parent::testSearch($name);
    }

    /**
     * Test Search for specific Vehicle with German Translations
     *
     * @param string $name The Vehicle Name
     */
    public function testSearchWithGermanTranslation(string $name)
    {
        $vehicle = $this->makeVehicleWithName($name);
        $vehicle->translations()->save(factory(VehicleTranslation::class)->state('german')->make());

        parent::testSearch($name);
    }


    /**
     * Setup Vehicles
     */
    protected function setUp(): void
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
                'slug' => str_slug($name),
            ]
        );
        $vehicle->translations()->save(factory(VehicleTranslation::class)->make());

        return $vehicle;
    }
}
