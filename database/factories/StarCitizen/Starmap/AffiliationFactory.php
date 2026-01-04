<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\Affiliation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Affiliation>
 */
class AffiliationFactory extends Factory
{
    protected $model = Affiliation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => fake()->company(),
            'code' => strtoupper(fake()->lexify('???')),
            'color' => fake()->hexColor(),
            'membership_id' => fake()->numberBetween(1, 999999),
        ];
    }
}
