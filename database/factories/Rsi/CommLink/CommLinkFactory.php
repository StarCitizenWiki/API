<?php

declare(strict_types=1);

namespace Database\Factories\Rsi\CommLink;

use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series\Series;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CommLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $cigId = 12663;

        return [
            'cig_id' => $cigId++,

            'title' => $this->faker->sentence(),
            'comment_count' => $this->faker->numberBetween(0, 2000),
            'url' => $this->faker->boolean() ? '/comm-link/SCW/' . $cigId . '-IMPORT' : null,

            'file' => Carbon::now()->format('Y-m-d_His') . '.html',

            'channel_id' => Channel::factory(),
            'category_id' => Category::factory(),
            'series_id' => Series::factory(),

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function german()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'locale_code' => 'de_DE',
                    'translation' => $this->faker->randomHtml(2, 3),
                ];
            }
        );
    }
}
