<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => fake()->words(2, true),
            'slug' => fake()->slug(),
            'thumbnail' => fake()->imageUrl(),
        ];
    }
}
