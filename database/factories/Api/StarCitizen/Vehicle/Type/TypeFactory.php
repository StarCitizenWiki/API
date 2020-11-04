<?php

declare(strict_types=1);

namespace Database\Factories\Api\StarCitizen\Vehicle\Type;

use App\Models\Api\StarCitizen\Vehicle\Type\Type;
use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Type::class;

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
            function (Type $size) {
                $size->translations()->save(
                    TypeTranslation::factory()->make()
                );
            }
        );
    }
}
