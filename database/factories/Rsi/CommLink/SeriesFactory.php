<?php

declare(strict_types=1);

namespace Database\Factories\Rsi\CommLink;

use App\Models\Rsi\CommLink\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Series>
 */
class SeriesFactory extends Factory
{
    protected $model = Series::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
        ];
    }
}
