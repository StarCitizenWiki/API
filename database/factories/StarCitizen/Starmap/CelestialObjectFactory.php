<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\CelestialObject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CelestialObject>
 */
class CelestialObjectFactory extends Factory
{
    protected $model = CelestialObject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(1000, 999999),
            'starsystem_id' => fake()->numberBetween(1000, 999999),
            'age' => fake()->randomFloat(2, 0, 10_000),
            'appearance' => fake()->word(),
            'axial_tilt' => fake()->randomFloat(2, 0, 45),
            'code' => strtoupper(fake()->lexify('?????')),
            'designation' => strtoupper(fake()->bothify('?#-###')),
            'distance' => fake()->randomFloat(2, 0, 100_000),
            'fairchanceact' => fake()->boolean(),
            'habitable' => fake()->boolean(),
            'info_url' => fake()->url(),
            'latitude' => fake()->randomFloat(6, -90, 90),
            'longitude' => fake()->randomFloat(6, -180, 180),
            'name' => fake()->city(),
            'orbit_period' => fake()->randomFloat(2, 0, 100_000),
            'parent_id' => fake()->numberBetween(1000, 999999),
            'sensor_danger' => fake()->randomFloat(2, 0, 10),
            'sensor_economy' => fake()->randomFloat(2, 0, 10),
            'sensor_population' => fake()->randomFloat(2, 0, 10),
            'size' => fake()->randomFloat(2, 0, 10_000),
            'type' => fake()->word(),
            'subtype_id' => null,
            'time_modified' => now(),
            'translation' => [],
        ];
    }
}
