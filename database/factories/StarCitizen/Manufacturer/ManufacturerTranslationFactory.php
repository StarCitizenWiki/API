<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\Manufacturer;

use App\Models\StarCitizen\Manufacturer\ManufacturerTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManufacturerTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ManufacturerTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'locale_code' => 'en_EN',
            'known_for' => 'Lorem Ipsum',
            'description' => 'Lorem Ipsum dolor sit amet',
        ];
    }

    public function german()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'locale_code' => 'de_DE',
                    'known_for' => 'Deutsches Lorem Ipsum',
                    'description' => 'Deutsches Lorem Ipsum',
                ];
            }
        );
    }
}
