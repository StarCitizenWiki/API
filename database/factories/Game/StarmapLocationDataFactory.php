<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StarmapLocationData>
 */
class StarmapLocationDataFactory extends Factory
{
    protected $model = StarmapLocationData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'starmap_location_id' => StarmapLocation::factory(),
            'game_version_id' => GameVersion::factory(),
            'parent_data_id' => null,
            'star_data_id' => null,
            'location_hierarchy_entity_tag_id' => null,
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'type_name' => fake()->randomElement(['SolarSystem', 'Planet', 'Outpost', 'Manmade']),
            'system' => fake()->randomElement(['Stanton', 'Pyro', 'Nyx']),
            'size' => fake()->randomFloat(2, 0.1, 1000),
            'is_scannable' => fake()->boolean(),
            'block_travel' => fake()->boolean(),
            'data' => [
                'type' => [
                    'classification' => fake()->randomElement(['Solar System', 'Planet', 'Outpost', 'Manmade']),
                ],
                'respawnLocationType' => fake()->randomElement(['None', 'Hospital', 'Other']),
                'hideInStarmap' => fake()->boolean(),
                'hideInWorld' => fake()->boolean(),
                'quantumTravel' => [
                    'arrivalRadius' => fake()->numberBetween(0, 10000),
                ],
            ],
        ];
    }

    public function withHierarchyTag(): self
    {
        return $this->state(fn (): array => [
            'location_hierarchy_entity_tag_id' => EntityTag::factory(),
        ]);
    }
}
