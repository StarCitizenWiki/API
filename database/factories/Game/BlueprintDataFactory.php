<?php

declare(strict_types=1);

namespace Database\Factories\Game;

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
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
            'ingredient_resource_type_uuids' => [],
            'data' => [
                'UUID' => fake()->uuid(),
                'Output' => [
                    'UUID' => $outputItemUuid,
                    'Name' => $outputName,
                    'Class' => $outputClass,
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
                                    'uuid' => fake()->uuid(),
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

    public function withIngredients(Commodity ...$commodities): self
    {
        return $this->afterCreating(static function (BlueprintData $blueprintData) use ($commodities): void {
            if ($commodities === []) {
                return;
            }

            $blueprintData->ingredients()->sync(
                collect($commodities)->pluck('id')->all()
            );
        });
    }

    public function withDismantleReturns(array $returns): self
    {
        return $this->afterCreating(static function (BlueprintData $blueprintData) use ($returns): void {
            if ($returns === []) {
                return;
            }

            $syncData = [];
            foreach ($returns as $return) {
                $syncData[$return['commodity']->id] = [
                    'quantity_scu' => $return['quantity_scu'],
                ];
            }

            $blueprintData->dismantleReturns()->sync($syncData);
        });
    }
}
