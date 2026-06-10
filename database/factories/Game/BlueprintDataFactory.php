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
            'unlocking_missions_count' => 0,
            'data' => [
                'UUID' => fake()->uuid(),
                'Output' => [
                    'UUID' => $outputItemUuid,
                    'Name' => $outputName,
                    'Class' => $outputClass,
                ],
                'Tiers' => [
                    [
                        'TierIndex' => 0,
                        'CraftTimeSeconds' => 120,
                        'Requirements' => [
                            'Kind' => 'root',
                            'Children' => [
                                [
                                    'Kind' => 'resource',
                                    'UUID' => fake()->uuid(),
                                    'Name' => fake()->word(),
                                    'QuantityScu' => 1.5,
                                    'MinQuality' => 0,
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
