<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Vehicle\Type;

use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TypeTranslation::class;

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
