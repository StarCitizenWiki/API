<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\ProductionNote;

use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionNote\ProductionNoteTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionNoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionNote::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(
            function (ProductionNote $productionNote) {
                $productionNote->translations()->save(
                    ProductionNoteTranslation::factory()->make()
                );
            }
        );
    }
}
