<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Faction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faction>
 */
class FactionFactory extends Factory
{
    protected $model = Faction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'name' => fake()->company(),
            'default_reaction' => fake()->randomElement(['Neutral', 'Friendly', 'Hostile']),
            'faction_type' => fake()->randomElement(['organization', 'npc', 'player']),
            'able_to_arrest' => fake()->boolean(),
            'polices_lawful_trespass' => fake()->boolean(),
            'polices_criminality' => fake()->boolean(),
            'no_legal_rights' => fake()->boolean(),
            'has_reputation' => fake()->boolean(),
            'lawful' => fake()->boolean(),
            'is_npc' => fake()->boolean(),
            'hide_in_delphi_app' => false,
        ];
    }
}
