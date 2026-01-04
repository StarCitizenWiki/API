<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    protected $model = Template::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template' => fake()->word(),
        ];
    }
}
