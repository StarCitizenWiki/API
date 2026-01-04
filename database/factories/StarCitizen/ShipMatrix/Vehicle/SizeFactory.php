<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\ShipMatrix\Vehicle;

use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Size>
 */
class SizeFactory extends Factory
{
    protected $model = Size::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->slug(),
            'translation' => [],
        ];
    }
}
