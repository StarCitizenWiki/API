<?php

declare(strict_types=1);

namespace Database\Factories\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $dir = Str::random(14);
        $fileName = Str::random(4);

        $file = sprintf('/media/%s/source/%s.jpg', $dir, $fileName);

        return [
            'src' => $file,
            'alt' => $this->faker->boolean() ? 'Lorem Ipsum' : '',
            'local' => false,
            'dir' => $dir,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
