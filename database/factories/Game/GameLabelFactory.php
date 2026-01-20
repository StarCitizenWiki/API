<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\GameLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameLabelFactory extends Factory
{
    protected $model = GameLabel::class;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->uuid(),
            'key' => fake()->unique()->word().'_'.fake()->word(),
            'translation' => [
                'en' => fake()->sentence(),
            ],
        ];
    }

    public function asItemDescTest(): self
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'item_Desc_test',
            'translation' => [
                'en' => 'English description',
                'zh' => '中文描述',
                'de' => 'Deutsche Beschreibung',
            ],
        ]);
    }
}
