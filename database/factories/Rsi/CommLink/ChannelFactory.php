<?php

declare(strict_types=1);

namespace Database\Factories\Rsi\CommLink;

use App\Models\Rsi\CommLink\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Channel>
 */
class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
        ];
    }
}
