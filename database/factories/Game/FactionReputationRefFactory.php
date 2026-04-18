<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Faction;
use App\Models\Game\FactionReputationRef;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FactionReputationRef>
 */
class FactionReputationRefFactory extends Factory
{
    protected $model = FactionReputationRef::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faction_id' => Faction::factory(),
        ];
    }
}
