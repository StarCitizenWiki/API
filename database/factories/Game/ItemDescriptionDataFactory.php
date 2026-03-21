<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Item;
use App\Models\Game\ItemDescriptionData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemDescriptionData>
 */
class ItemDescriptionDataFactory extends Factory
{
    protected $model = ItemDescriptionData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'name' => fake()->randomElement(['Type', 'Manufacturer', 'Focus', 'Role']),
            'value' => fake()->words(3, true),
        ];
    }
}
