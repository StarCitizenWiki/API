<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\ShipMatrix;

use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionNote>
 */
class ProductionNoteFactory extends Factory
{
    protected $model = ProductionNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translation' => [],
        ];
    }
}
