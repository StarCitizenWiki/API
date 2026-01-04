<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Starsystem>
 */
class StarsystemFactory extends Factory
{
    protected $model = Starsystem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(1000, 999999),
            'code' => strtoupper(fake()->lexify('???')),
            'status' => fake()->randomElement(['ACTIVE', 'INACTIVE', 'UNKNOWN']),
            'info_url' => fake()->url(),
            'name' => fake()->city(),
            'type' => fake()->word(),
            'position_x' => fake()->randomFloat(2, -1000, 1000),
            'position_y' => fake()->randomFloat(2, -1000, 1000),
            'position_z' => fake()->randomFloat(2, -1000, 1000),
            'frost_line' => fake()->randomFloat(2, 0, 1000),
            'habitable_zone_inner' => fake()->randomFloat(2, 0, 1000),
            'habitable_zone_outer' => fake()->randomFloat(2, 0, 1000),
            'aggregated_size' => fake()->randomFloat(2, 0, 1000),
            'aggregated_population' => fake()->randomFloat(2, 0, 1000),
            'aggregated_economy' => fake()->randomFloat(2, 0, 1000),
            'aggregated_danger' => fake()->numberBetween(0, 10),
            'time_modified' => now(),
            'translation' => [],
        ];
    }
}
