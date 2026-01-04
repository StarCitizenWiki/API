<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\ShipMatrix\Vehicle;

use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Type>
 */
class TypeFactory extends Factory
{
    protected $model = Type::class;

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
