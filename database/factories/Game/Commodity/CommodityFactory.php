<?php

declare(strict_types=1);

namespace Database\Factories\Game\Commodity;

use App\Models\Game\Commodity\Commodity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commodity>
 */
class CommodityFactory extends Factory
{
    protected $model = Commodity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'slug' => fake()->unique()->slug(2),
            'key' => fake()->unique()->bothify('Commodity_####'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'refined_version_uuid' => null,
            'validate_default_cargo_box' => fake()->boolean(),
            'has_default_cargo_containers' => fake()->boolean(),
            'box_sizes_scu' => [1, 2, 4],
            'instability' => fake()->optional()->randomFloat(4, 0, 1000),
            'resistance' => fake()->optional()->randomFloat(4, -1, 1),
            'density_g_per_cc' => fake()->optional()->randomFloat(4, 1, 25),
            'data' => [
                'uuid' => fake()->uuid(),
                'key' => fake()->bothify('Data_####'),
            ],
        ];
    }
}
