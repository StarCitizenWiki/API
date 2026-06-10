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
            'location_uuid' => null,
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

    public function configure(): self
    {
        return $this
            ->afterMaking(static function (StarmapLocationData $locationData): void {
                if ($locationData->location_uuid === null && $locationData->starmap_location_id !== null) {
                    $location = StarmapLocation::find($locationData->starmap_location_id);
                    $locationData->location_uuid = $location?->uuid;
                    $locationData->location_slug = $location?->slug;
                }
            })
            ->afterCreating(static function (StarmapLocationData $locationData): void {
                $dirty = false;

                // Backfill parent denorm columns
                if ($locationData->parent_data_id !== null && $locationData->parent_name === null) {
                    $parent = StarmapLocationData::find($locationData->parent_data_id);
                    if ($parent !== null) {
                        $locationData->parent_name = $parent->name;
                        $locationData->parent_type_name = $parent->type_name;
                        $locationData->parent_location_uuid = $parent->location_uuid;
                        $locationData->parent_location_slug = $locationData->parent_location_slug ?? $parent->location_slug;
                        $dirty = true;
                    }
                }

                // Backfill star denorm columns
                if ($locationData->star_data_id !== null && $locationData->star_system_name === null) {
                    $star = StarmapLocationData::find($locationData->star_data_id);
                    if ($star !== null) {
                        $locationData->star_system_name = $star->name;
                        $locationData->star_name = $locationData->star_name ?? $star->name;
                        $locationData->star_location_uuid = $locationData->star_location_uuid ?? $star->location_uuid;
                        $locationData->star_location_slug = $locationData->star_location_slug ?? $star->location_slug;
                        $dirty = true;
                    }
                }

                // Backfill JSON-extracted denorm columns from data
                $data = $locationData->data ?? [];

                if ($locationData->type_classification === null) {
                    $locationData->type_classification = trim((string) ($data['Type']['Classification'] ?? '')) ?: null;
                    $dirty = true;
                }

                if ($locationData->jurisdiction_name === null) {
                    $locationData->jurisdiction_name = trim((string) ($data['Jurisdiction']['Name'] ?? '')) ?: null;
                    $dirty = true;
                }

                if ($locationData->affiliation_name === null) {
                    $locationData->affiliation_name = trim((string) ($data['Affiliation']['DisplayName'] ?? '')) ?: null;
                    $dirty = true;
                }

                if ($locationData->respawn_location_type === null) {
                    $locationData->respawn_location_type = trim((string) ($data['RespawnLocationType'] ?? '')) ?: null;
                    $dirty = true;
                }

                if (! $locationData->hide_in_starmap) {
                    $locationData->hide_in_starmap = filter_var($data['HideInStarmap'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $dirty = true;
                }

                if (! $locationData->hide_in_world) {
                    $locationData->hide_in_world = filter_var($data['HideInWorld'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $dirty = true;
                }

                if (! $locationData->hide_minor_locations) {
                    $locationData->hide_minor_locations = filter_var($data['OnlyShowWhenParentSelected'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $dirty = true;
                }

                // Backfill tag denorm columns
                if ($locationData->location_hierarchy_entity_tag_id !== null && $locationData->tag_name === null) {
                    $tag = EntityTag::find($locationData->location_hierarchy_entity_tag_id);
                    if ($tag !== null) {
                        $locationData->tag_uuid = $tag->uuid;
                        $locationData->tag_name = $tag->name;
                        $dirty = true;
                    }
                }

                if ($dirty) {
                    $locationData->saveQuietly();
                }

                // Update parent's child_count (version-scoped)
                if ($locationData->parent_data_id !== null) {
                    $parent = StarmapLocationData::find($locationData->parent_data_id);
                    if ($parent !== null) {
                        $parent->child_count = StarmapLocationData::query()
                            ->where('parent_data_id', $parent->id)
                            ->where('game_version_id', $parent->game_version_id)
                            ->count();
                        $parent->saveQuietly();
                    }
                }
            });
    }
}
