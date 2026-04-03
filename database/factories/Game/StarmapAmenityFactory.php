<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\StarmapAmenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StarmapAmenity>
 */
class StarmapAmenityFactory extends Factory
{
    protected $model = StarmapAmenity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'name' => fake()->words(2, true),
            'display_name' => fake()->words(2, true),
        ];
    }
}
