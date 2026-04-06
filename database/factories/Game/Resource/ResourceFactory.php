<?php

declare(strict_types=1);

namespace Database\Factories\Game\Resource;

use App\Models\Game\Resource\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<resource>
 */
class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
        ];
    }
}
