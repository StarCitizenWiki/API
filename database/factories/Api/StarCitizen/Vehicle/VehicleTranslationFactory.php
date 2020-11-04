<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VehicleTranslation::class;

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

    public function german()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'locale_code' => 'de_DE',
                    'translation' => 'Deutsches Lorem Ipsum',
                ];
            }
        );
    }

    public function english()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'locale_code' => 'en_EN',
                    'translation' => 'Lorem Ipsum dolor sit amet',
                ];
            }
        );
    }
}
