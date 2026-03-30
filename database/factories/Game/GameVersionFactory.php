<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\GameVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameVersion>
 */
class GameVersionFactory extends Factory
{
    protected $model = GameVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('3.##.#'),
            'channel' => fake()->randomElement(['live', 'ptu', 'eptu']),
            'released_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'is_default' => false,
            'is_hidden' => false,
        ];
    }
}
