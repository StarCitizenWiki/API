<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cig_id' => fake()->unique()->numberBetween(10000, 999999),
            'title' => fake()->sentence(3),
            'slug' => fake()->slug(),
            'in_wiki' => true,
            'disabled' => false,
            'thumbnail' => fake()->imageUrl(),
            'translation' => [],
        ];
    }
}
