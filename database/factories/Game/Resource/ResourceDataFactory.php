<?php

declare(strict_types=1);

namespace Database\Factories\Game\Resource;

use App\Models\Game\GameVersion;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceData>
 */
class ResourceDataFactory extends Factory
{
    protected $model = ResourceData::class;

    public function definition(): array
    {
        return [
            'resource_id' => Resource::factory(),
            'game_version_id' => GameVersion::factory(),
            'key' => fake()->unique()->bothify('Resource_####??'),
            'name' => fake()->words(2, true),
            'kind' => fake()->randomElement(['salvage', 'harvestable', 'mineable']),
            'tier' => fake()->optional()->randomElement(['common', 'uncommon', 'rare', 'epic', 'legendary']),
            'signature' => fake()->optional()->numberBetween(0, 5000),
            'data' => null,
        ];
    }
}
