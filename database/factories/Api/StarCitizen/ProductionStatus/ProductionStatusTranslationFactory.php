<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\ProductionStatus;

use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionStatusTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionStatusTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
}
