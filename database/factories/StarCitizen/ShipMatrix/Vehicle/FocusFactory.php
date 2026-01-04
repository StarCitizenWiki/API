<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\ShipMatrix\Vehicle;

use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Focus>
 */
class FocusFactory extends Factory
{
    protected $model = Focus::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->slug(),
            'translation' => [],
        ];
    }
}
