<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\StarmapLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StarmapLocation>
 */
class StarmapLocationFactory extends Factory
{
    protected $model = StarmapLocation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
        ];
    }
}
