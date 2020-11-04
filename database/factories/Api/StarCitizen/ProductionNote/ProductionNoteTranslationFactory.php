<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\ProductionNote;

use App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionNoteTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionNoteTranslation::class;

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
