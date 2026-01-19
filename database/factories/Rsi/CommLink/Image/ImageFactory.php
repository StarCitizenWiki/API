<?php

declare(strict_types=1);

namespace Database\Factories\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Image>
 */
class ImageFactory extends Factory
{
    protected $model = Image::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $basename = fake()->uuid();

        return [
            'src' => sprintf('/i/%s/%s.webp', $basename, $basename),
            'alt' => fake()->words(3, true),
            'local' => false,
            'dir' => 'i',
            'base_image_id' => null,
        ];
    }
}
