<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Vehicle\Size;

use App\Models\StarCitizen\Vehicle\Size\SizeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class SizeTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SizeTranslation::class;

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
