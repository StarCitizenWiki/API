<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Vehicle\Focus;

use App\Models\Api\StarCitizen\Vehicle\Focus\Focus;
use App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FocusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Focus::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'slug' => $this->faker->unique()->slug,
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
            function (Focus $focus) {
                $focus->translations()->save(
                    FocusTranslation::factory()->make()
                );
            }
        );
    }
}
