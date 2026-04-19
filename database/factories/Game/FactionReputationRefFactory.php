<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Faction;
use App\Models\Game\FactionReputationRef;
use App\Models\Game\FactionScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FactionReputationRef>
 */
class FactionReputationRefFactory extends Factory
{
    protected $model = FactionReputationRef::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faction_id' => Faction::factory(),
            'faction_scope_id' => FactionScope::factory(),
        ];
    }
}
