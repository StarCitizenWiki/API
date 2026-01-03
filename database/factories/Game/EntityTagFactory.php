<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\EntityTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game\EntityTag>
 */
class EntityTagFactory extends Factory
{
    protected $model = EntityTag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'name' => fake()->randomElement(['Armor', 'Weapon', 'Food', 'Clothing', 'Ship', 'Component']),
        ];
    }
}
