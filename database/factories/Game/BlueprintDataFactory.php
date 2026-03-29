<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlueprintData>
 */
class BlueprintDataFactory extends Factory
{
    protected $model = BlueprintData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $outputItemUuid = fake()->uuid();
        $outputName = fake()->words(3, true);
        $outputClass = fake()->unique()->bothify('bp_output_####');
        $ingredientResourceTypeUuid = fake()->uuid();

        return [
            'blueprint_id' => Blueprint::factory(),
            'game_version_id' => GameVersion::factory(),
            'key' => fake()->unique()->bothify('BP_CRAFT_########'),
            'category_uuid' => fake()->uuid(),
            'output_item_uuid' => $outputItemUuid,
            'output_name' => $outputName,
            'output_class' => $outputClass,
            'craft_time_seconds' => fake()->numberBetween(10, 600),
            'is_available_by_default' => fake()->boolean(),
            'ingredient_resource_type_uuids' => [$ingredientResourceTypeUuid],
            'data' => [
                'uuid' => fake()->uuid(),
                'output' => [
                    'uuid' => $outputItemUuid,
                    'name' => $outputName,
                    'class' => $outputClass,
                ],
                'tiers' => [
                    [
                        'tier_index' => 0,
                        'craft_time_seconds' => 120,
                        'requirements' => [
                            'kind' => 'root',
                            'children' => [
                                [
                                    'kind' => 'resource',
                                    'uuid' => $ingredientResourceTypeUuid,
                                    'name' => fake()->word(),
                                    'quantity_scu' => 1.5,
                                    'min_quality' => 0,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
