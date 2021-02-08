<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Vehicle\Size;

use App\Models\Api\StarCitizen\Vehicle\Size\Size;
use App\Models\Api\StarCitizen\Vehicle\Size\SizeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class SizeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Size::class;

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
            function (Size $size) {
                $size->translations()->save(
                    SizeTranslation::factory()->make()
                );
            }
        );
    }
}
