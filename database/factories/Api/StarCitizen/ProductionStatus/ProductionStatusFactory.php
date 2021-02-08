<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\ProductionStatus;

use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(
            function (ProductionStatus $productionStatus) {
                $productionStatus->translations()->save(
                    ProductionStatusTranslation::factory()->make()
                );
            }
        );
    }
}
