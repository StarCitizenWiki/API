<?php

declare(strict_types=1);

namespace Database\Factories\Game\Mission;

use App\Models\Game\Mission\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mission>
 */
class MissionFactory extends Factory
{
    protected $model = Mission::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
        ];
    }
}
