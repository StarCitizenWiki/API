<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\ItemData;
use App\Models\Game\ItemTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game\ItemTranslation>
 */
class ItemTranslationFactory extends Factory
{
    protected $model = ItemTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'locale_code' => fake()->randomElement(['en', 'de', 'fr', 'es']),
            'item_data_id' => ItemData::factory(),
            'translation' => fake()->sentence(),
        ];
    }
}
