<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\ShipMatrix\Vehicle;

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'manufacturer_id' => Manufacturer::factory(),
            'production_status_id' => ProductionStatus::factory(),
            'production_note_id' => ProductionNote::factory(),
            'size_id' => Size::factory(),
            'type_id' => Type::factory(),
            'length' => fake()->randomFloat(2, 10, 200),
            'beam' => fake()->randomFloat(2, 10, 200),
            'height' => fake()->randomFloat(2, 10, 200),
            'mass' => fake()->numberBetween(1000, 100000),
            'cargo_capacity' => fake()->randomFloat(2, 0, 1000),
            'min_crew' => fake()->numberBetween(1, 4),
            'max_crew' => fake()->numberBetween(1, 8),
            'scm_speed' => fake()->numberBetween(50, 500),
            'afterburner_speed' => fake()->numberBetween(100, 800),
            'pitch_max' => fake()->randomFloat(2, 1, 10),
            'yaw_max' => fake()->randomFloat(2, 1, 10),
            'roll_max' => fake()->randomFloat(2, 1, 10),
            'x_axis_acceleration' => fake()->randomFloat(2, 1, 10),
            'y_axis_acceleration' => fake()->randomFloat(2, 1, 10),
            'z_axis_acceleration' => fake()->randomFloat(2, 1, 10),
            'chassis_id' => fake()->numberBetween(1, 999999),
            'msrp' => fake()->numberBetween(10000, 1000000),
            'pledge_url' => fake()->url(),
            'translation' => [],
        ];
    }
}
