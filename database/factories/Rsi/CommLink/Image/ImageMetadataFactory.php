<?php

declare(strict_types=1);

namespace Database\Factories\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\ImageMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageMetadataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ImageMetadata::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'size' => $this->faker->randomNumber(5),
            'mime' => 'image/jpeg',
            'last_modified' => $this->faker->dateTime(),
        ];
    }
}
