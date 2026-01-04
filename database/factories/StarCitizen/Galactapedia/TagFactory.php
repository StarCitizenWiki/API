<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => fake()->word(),
            'slug' => fake()->slug(),
        ];
    }
}
