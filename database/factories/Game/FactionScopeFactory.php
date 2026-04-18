<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\FactionScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FactionScope>
 */
class FactionScopeFactory extends Factory
{
    protected $model = FactionScope::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'scope_name' => fake()->word(),
            'display_name' => fake()->words(2, true),
            'reputation_ceiling' => fake()->numberBetween(0, 10000),
            'initial_reputation' => fake()->numberBetween(0, 1000),
        ];
    }
}
