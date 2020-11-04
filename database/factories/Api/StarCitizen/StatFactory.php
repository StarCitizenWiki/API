<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen;

use App\Models\Api\StarCitizen\Stat\Stat;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Stat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'funds' => $this->faker->numberBetween(1000000, mt_getrandmax()),
            'fleet' => $this->faker->numberBetween(1000, 1500000),
            'fans' => $this->faker->numberBetween(1000, 2000000),
        ];
    }
}
