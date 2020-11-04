<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\Api\StarCitizen\Vehicle\Size\Size;
use App\Models\Api\StarCitizen\Vehicle\Type\Type;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $cigId = 1;

        $name = $this->faker->unique()->userName;
        $slug = Str::slug($name);

        return [
            'cig_id' => $cigId++,
            'name' => $name,
            'slug' => $slug,
            'manufacturer_id' => Manufacturer::factory(),
            'production_status_id' => ProductionStatus::factory(),
            'production_note_id' => ProductionNote::factory(),
            'size_id' => Size::factory(),
            'type_id' => Type::factory(),
            'length' => $this->faker->randomFloat(2, 0, 1000),
            'beam' => $this->faker->randomFloat(2, 0, 1000),
            'height' => $this->faker->randomFloat(2, 0, 1000),
            'mass' => $this->faker->numberBetween(0, 1000),
            'cargo_capacity' => $this->faker->numberBetween(0, 1000),
            'min_crew' => $this->faker->numberBetween(0, 1000),
            'max_crew' => $this->faker->numberBetween(0, 1000),
            'scm_speed' => $this->faker->numberBetween(0, 1000),
            'afterburner_speed' => $this->faker->numberBetween(0, 1000),
            'pitch_max' => $this->faker->randomFloat(2, 0, 1000),
            'yaw_max' => $this->faker->randomFloat(2, 0, 1000),
            'roll_max' => $this->faker->randomFloat(2, 0, 1000),
            'x_axis_acceleration' => $this->faker->randomFloat(2, 0, 1000),
            'y_axis_acceleration' => $this->faker->randomFloat(2, 0, 1000),
            'z_axis_acceleration' => $this->faker->randomFloat(2, 0, 1000),
            'chassis_id' => $this->faker->numberBetween(0, 1000),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(
            function (Vehicle $vehicle) {
                $vehicle->translations()->save(
                    VehicleTranslation::factory()->make()
                );
            }
        );
    }

    public function groundVehicle()
    {
        return $this->state(
            function (array $attributes) {
                try {
                    /** @var Type $type */
                    $type = Type::where('slug', 'ground')->firstorFail();
                    /** @var Size $size */
                    $size = Size::where('slug', 'vehicle')->firstorFail();
                } catch (ModelNotFoundException $e) {
                    /** @var Type $type */
                    $type = Type::factory()->create(
                        [
                            'slug' => 'ground',
                        ]
                    );
                    /** @var Size $type */
                    $size = Size::factory()->create(
                        [
                            'slug' => 'vehicle',
                        ]
                    );
                }

                return [
                    'type_id' => $type->id,
                    'size_id' => $size->id,
                ];
            }
        );
    }

    public function ship()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'type_id' => Type::factory(),
                ];
            }
        );
    }
}
