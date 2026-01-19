<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game\Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    protected $model = Manufacturer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'name' => fake()->company(),
            'code' => strtoupper(fake()->lexify('???')),
        ];
    }
}
