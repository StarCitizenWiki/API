<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Blueprint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Blueprint>
 */
class BlueprintFactory extends Factory
{
    protected $model = Blueprint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'slug' => fn () => fake()->unique()->slug(2),
        ];
    }
}
