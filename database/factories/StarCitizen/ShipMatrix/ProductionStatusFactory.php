<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\ShipMatrix;

use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionStatus>
 */
class ProductionStatusFactory extends Factory
{
    protected $model = ProductionStatus::class;

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
