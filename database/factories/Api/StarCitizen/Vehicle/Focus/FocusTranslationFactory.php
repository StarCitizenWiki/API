<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Vehicle\Focus;

use App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FocusTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FocusTranslation::class;

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
