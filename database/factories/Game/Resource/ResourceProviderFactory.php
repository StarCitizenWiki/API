<?php

declare(strict_types=1);

namespace Database\Factories\Game\Resource;

use App\Models\Game\GameVersion;
use App\Models\Game\Resource\ResourceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceProvider>
 */
class ResourceProviderFactory extends Factory
{
    protected $model = ResourceProvider::class;

    public function definition(): array
    {
        return [
            'game_version_id' => GameVersion::factory(),
            'provider_name' => fake()->optional()->randomElement(['Loot_Caves_Unoccupied_Rock', 'Loot_Caves_Occupied_Sand', 'SpaceShip_Mineables_Stanton', 'GroundVehicle_Mineables']),
            'areas' => null,
        ];
    }
}
