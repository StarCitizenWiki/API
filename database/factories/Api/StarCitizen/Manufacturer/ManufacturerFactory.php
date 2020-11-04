<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Manufacturer;

use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManufacturerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Manufacturer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $cigId = 1;

        return [
            'cig_id' => $cigId++,
            'name' => $this->faker->unique()->userName,
            'name_short' => $this->faker->unique()->userName,
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
            function (Manufacturer $manufacturer) {
                $manufacturer->translations()->save(
                    ManufacturerTranslation::factory()->make()
                );
            }
        );
    }
}
