<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle::class,
    function (Faker $faker) {
        static $cigId = 1;

        $manufacturer = factory(\App\Models\Api\StarCitizen\Manufacturer\Manufacturer::class)->create();
        $manufacturer->translations()->save(
            factory(\App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation::class)->make()
        );

        $productionStatus = factory(\App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus::class)->create();
        $productionStatus->translations()->save(
            factory(\App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation::class)->make()
        );

        $productionNote = factory(\App\Models\Api\StarCitizen\ProductionNote\ProductionNote::class)->create();
        $productionNote->translations()->save(
            factory(\App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation::class)->make()
        );

        $vehicleSize = factory(\App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize::class)->create();
        $vehicleSize->translations()->save(
            factory(\App\Models\Api\StarCitizen\Vehicle\Size\VehicleSizeTranslation::class)->make()
        );

        $vehicleType = factory(\App\Models\Api\StarCitizen\Vehicle\Type\VehicleType::class)->create();
        $vehicleType->translations()->save(
            factory(\App\Models\Api\StarCitizen\Vehicle\Type\VehicleTypeTranslation::class)->make()
        );

        $vehicleFocus = factory(\App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus::class)->create();
        $vehicleFocus->translations()->save(
            factory(\App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocusTranslation::class)->make()
        );

        return [
            'cig_id' => $cigId++,
            'name' => $faker->userName,
            'manufacturer_id' => $manufacturer->id,
            'production_status_id' => $productionStatus->id,
            'production_note_id' => $productionNote->id,
            'vehicle_size_id' => $vehicleSize->id,
            'vehicle_type_id' => $vehicleType->id,
            'length' => $faker->randomFloat(2, 0, 1000),
            'beam' => $faker->randomFloat(2, 0, 1000),
            'height' => $faker->randomFloat(2, 0, 1000),
            'mass' => $faker->numberBetween(0, 1000),
            'cargo_capacity' => $faker->numberBetween(0, 1000),
            'min_crew' => $faker->numberBetween(0, 1000),
            'max_crew' => $faker->numberBetween(0, 1000),
            'scm_speed' => $faker->numberBetween(0, 1000),
            'afterburner_speed' => $faker->numberBetween(0, 1000),
            'pitch_max' => $faker->randomFloat(2, 0, 1000),
            'yaw_max' => $faker->randomFloat(2, 0, 1000),
            'roll_max' => $faker->randomFloat(2, 0, 1000),
            'x_axis_acceleration' => $faker->randomFloat(2, 0, 1000),
            'y_axis_acceleration' => $faker->randomFloat(2, 0, 1000),
            'z_axis_acceleration' => $faker->randomFloat(2, 0, 1000),
            'chassis_id' => $faker->numberBetween(0, 1000),
        ];
    }
);

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum dolor sit amet',
        ];
    }
);

$factory->state(
    \App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation::class,
    'german',
    function (Faker $faker) {
        return [
            'locale_code' => 'de_DE',
            'translation' => 'Lorem Ipsum dolor sit amet',
        ];
    }
);
