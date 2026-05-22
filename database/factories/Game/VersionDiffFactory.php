<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Item;
use App\Models\Game\VersionDiff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VersionDiff>
 */
class VersionDiffFactory extends Factory
{
    protected $model = VersionDiff::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_type' => Item::class,
            'entity_id' => 1,
            'change_type' => 'added',
            'column_changes' => null,
            'data_changes' => null,
        ];
    }
}
