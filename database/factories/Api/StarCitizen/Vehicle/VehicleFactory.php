<?php declare(strict_types = 1);

use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use App\Models\Api\StarCitizen\Vehicle\Focus\Focus;
use App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation;
use App\Models\Api\StarCitizen\Vehicle\Size\Size;
use App\Models\Api\StarCitizen\Vehicle\Size\SizeTranslation;
use App\Models\Api\StarCitizen\Vehicle\Type\Type;
use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

$factory->define(
    Vehicle::class,
    function (Faker $faker) {
        static $cigId = 1;

        $manufacturer = factory(Manufacturer::class)->create();
        $manufacturer->translations()->save(
            factory(ManufacturerTranslation::class)->make()
        );

        $productionStatus = factory(ProductionStatus::class)->create();
        $productionStatus->translations()->save(
            factory(ProductionStatusTranslation::class)->make()
        );

        $productionNote = factory(ProductionNote::class)->create();
        $productionNote->translations()->save(
            factory(ProductionNoteTranslation::class)->make()
        );

        $vehicleSize = factory(Size::class)->create();
        $vehicleSize->translations()->save(
            factory(SizeTranslation::class)->make()
        );

        $vehicleType = factory(Type::class)->create();
        $vehicleType->translations()->save(
            factory(TypeTranslation::class)->make()
        );

        $vehicleFocus = factory(Focus::class)->create();
        $vehicleFocus->translations()->save(
            factory(FocusTranslation::class)->make()
        );

        $name = $faker->unique()->userName;
        $slug = Str::slug($name);

        return [
            'cig_id' => $cigId++,
            'name' => $name,
            'slug' => $slug,
            'manufacturer_id' => $manufacturer->id,
            'production_status_id' => $productionStatus->id,
            'production_note_id' => $productionNote->id,
            'size_id' => $vehicleSize->id,
            'type_id' => $vehicleType->id,
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
    VehicleTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum dolor sit amet',
        ];
    }
);

$factory->state(
    VehicleTranslation::class,
    'german',
    function (Faker $faker) {
        return [
            'locale_code' => 'de_DE',
            'translation' => 'Deutsches Lorem Ipsum',
        ];
    }
);

$factory->state(
    Vehicle::class,
    'ground_vehicle',
    function (Faker $faker) {
        try {
            /** @var Type $type */
            $type = Type::where('slug', 'ground')->firstorFail();
            /** @var Size $size */
            $size = Size::where('slug', 'vehicle')->firstorFail();
        } catch (ModelNotFoundException $e) {
            /** @var Type $type */
            $type = factory(Type::class)->create(
                [
                    'slug' => 'ground',
                ]
            );
            /** @var Size $type */
            $size = factory(Size::class)->create(
                [
                    'slug' => 'vehicle',
                ]
            );
            $size->translations()->save(
                factory(SizeTranslation::class)->make(
                    [
                        'locale_code' => 'en_EN',
                        'translation' => 'vehicle',
                    ]
                )
            );
        }

        return [
            'type_id' => $type->id,
            'size_id' => $size->id,
        ];
    }
);

// State Definition needed in VehicleTestCase
$factory->state(
    Vehicle::class,
    'ship',
    function () {
        /** @var Type $type */
        $type = factory(Type::class)->create();
        $type->translations()->save(factory(TypeTranslation::class)->make());

        return [
            'type_id' => $type->id,
        ];
    }
);
