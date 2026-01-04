<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\ArticleProperty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleProperty>
 */
class ArticlePropertyFactory extends Factory
{
    protected $model = ArticleProperty::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'name' => fake()->word(),
            'content' => fake()->sentence(),
        ];
    }
}
