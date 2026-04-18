<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\FactionScope;
use App\Models\Game\FactionStanding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FactionStanding>
 */
class FactionStandingFactory extends Factory
{
    protected $model = FactionStanding::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'faction_scope_id' => FactionScope::factory(),
            'name' => fake()->word(),
            'display_name' => fake()->words(2, true),
            'min_reputation' => fake()->numberBetween(-10000, 10000),
            'drift_reputation' => fake()->numberBetween(0, 100),
            'drift_time_hours' => fake()->numberBetween(0, 720),
            'gated' => false,
        ];
    }
}
