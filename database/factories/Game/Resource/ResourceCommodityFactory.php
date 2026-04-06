<?php

declare(strict_types=1);

namespace Database\Factories\Game\Resource;

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Resource\ResourceCommodity;
use App\Models\Game\Resource\ResourceData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceCommodity>
 */
class ResourceCommodityFactory extends Factory
{
    protected $model = ResourceCommodity::class;

    public function definition(): array
    {
        return [
            'resource_data_id' => ResourceData::factory(),
            'commodity_id' => Commodity::factory(),
            'weight' => fake()->optional()->randomFloat(4, 0, 1),
            'min_percentage' => fake()->optional()->randomFloat(4, 10, 40),
            'max_percentage' => fake()->optional()->randomFloat(4, 50, 80),
            'probability' => fake()->optional()->randomFloat(4, 0, 1),
            'quality_scale' => fake()->optional()->randomFloat(4, 0.5, 2),
            'curve_exponent' => fake()->optional()->randomFloat(4, 0.5, 2),
        ];
    }
}
