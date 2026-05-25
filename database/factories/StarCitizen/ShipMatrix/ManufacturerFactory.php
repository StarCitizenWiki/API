<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\ShipMatrix;

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    protected $model = Manufacturer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => fake()->company(),
            'name_short' => strtoupper(fake()->unique()->lexify('????')),
            'known_for' => [],
            'description' => [],
        ];
    }
}
