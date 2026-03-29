<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\ResourceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceType>
 */
class ResourceTypeFactory extends Factory
{
    protected $model = ResourceType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'key' => fake()->unique()->bothify('ResourceType_####'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'refined_version_uuid' => null,
            'validate_default_cargo_box' => fake()->boolean(),
            'has_default_cargo_containers' => fake()->boolean(),
            'box_sizes_scu' => [1, 2, 4],
            'data' => [
                'uuid' => fake()->uuid(),
                'key' => fake()->bothify('Data_####'),
            ],
        ];
    }
}
