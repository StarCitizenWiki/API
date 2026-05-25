<?php

declare(strict_types=1);

namespace Database\Factories\Rsi\CommLink;

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommLink>
 */
class CommLinkFactory extends Factory
{
    protected $model = CommLink::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(12663, 99999),
            'title' => fake()->sentence(),
            'comment_count' => fake()->numberBetween(0, 100),
            'url' => fake()->url(),
            'file' => fake()->dateTime()->format('Y-m-d_His').'.html',
            'channel_id' => Channel::factory(),
            'category_id' => Category::factory(),
            'series_id' => Series::factory(),
        ];
    }
}
