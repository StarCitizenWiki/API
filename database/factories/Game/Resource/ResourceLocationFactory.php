<?php

declare(strict_types=1);

namespace Database\Factories\Game\Resource;

use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\Resource\ResourceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceLocation>
 */
class ResourceLocationFactory extends Factory
{
    protected $model = ResourceLocation::class;

    public function definition(): array
    {
        return [
            'resource_data_id' => ResourceData::factory(),
            'resource_provider_id' => ResourceProvider::factory(),
            'group_name' => fake()->randomElement(['Harvestables', 'Mineables', 'Salvage_BrokenShips_Poor', 'Rich_Deposits']),
            'group_probability' => fake()->randomFloat(6, 0, 1),
            'relative_probability' => fake()->randomFloat(10, 0, 1),
            'resource_kind' => fake()->randomElement(['mineable', 'harvestable', 'salvage', 'loot', 'remains', 'fossil']),
            'commodity_id' => null,
            'quality_min' => fake()->optional()->numberBetween(1, 500),
            'quality_max' => fake()->optional()->numberBetween(501, 1000),
            'quality_mean' => fake()->optional()->numberBetween(50, 500),
            'quality_stddev' => fake()->optional()->numberBetween(10, 300),
            'min_percentage' => fake()->optional()->randomFloat(4, 0, 100),
            'max_percentage' => fake()->optional()->randomFloat(4, 0, 100),
            'data' => null,
        ];
    }
}
