<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\Jumppoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jumppoint>
 */
class JumppointFactory extends Factory
{
    protected $model = Jumppoint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(1000, 999999),
            'direction' => fake()->randomElement(['ONE_WAY', 'TWO_WAY']),
            'entry_id' => fake()->numberBetween(1000, 999999),
            'exit_id' => fake()->numberBetween(1000, 999999),
            'name' => fake()->word().' Jump',
            'size' => fake()->randomElement(['SMALL', 'MEDIUM', 'LARGE', 'HUGE']),
            'entry_status' => fake()->randomElement(['ACTIVE', 'INACTIVE', 'DANGEROUS']),
            'exit_status' => fake()->randomElement(['ACTIVE', 'INACTIVE', 'DANGEROUS']),
        ];
    }
}
