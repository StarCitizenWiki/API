<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;

if (! function_exists('minimalBlueprintData')) {
    /**
     * Returns the minimal nested data skeleton for a BlueprintData test fixture.
     * Each test overrides only the fields it asserts on.
     */
    function minimalBlueprintData(Commodity $ingredient, array $overrides = []): array
    {
        $defaultOutputUuid = $overrides['output_item_uuid'] ?? fake()->uuid();
        $defaultIngredientUuid = $overrides['_ingredient_uuid'] ?? $ingredient->uuid;
        $defaultIngredientName = $overrides['_ingredient_name'] ?? $ingredient->name;

        $tiers = $overrides['tiers'] ?? [
            [
                'requirements' => [
                    'kind' => 'root',
                    'children' => [
                        [
                            'kind' => 'resource',
                            'uuid' => $defaultIngredientUuid,
                            'name' => $defaultIngredientName,
                            'quantity_scu' => 0.03,
                        ],
                    ],
                ],
            ],
        ];

        unset($overrides['_ingredient_uuid'], $overrides['_ingredient_name'], $overrides['tiers']);

        return array_merge([
            'key' => 'BP_CRAFT_TEST',
            'output_item_uuid' => $defaultOutputUuid,
            'output_name' => 'Test Output',
            'output_class' => 'test_output',
            'craft_time_seconds' => 120,
            'is_available_by_default' => true,
            'data' => [
                'output' => [
                    'uuid' => $defaultOutputUuid,
                    'name' => 'Test Output',
                    'class' => 'test_output',
                ],
                'tiers' => $tiers,
            ],
        ], $overrides);
    }
}
