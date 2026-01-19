<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'food_nutrition',
    title: 'Food Nutrition',
    description: 'Dynamic map of nutrient name (snake_case) to total amount for the item.',
    type: 'object',
    additionalProperties: new OA\AdditionalProperties(type: 'number')
)]
#[OA\Schema(
    schema: 'food_effect_durations',
    title: 'Food Effect Durations',
    description: 'Dynamic map of effect/buff type (snake_case) to duration in seconds.',
    type: 'object',
    additionalProperties: new OA\AdditionalProperties(type: 'number')
)]
#[OA\Schema(
    schema: 'food_container',
    title: 'Food Container',
    description: 'Container metadata from stdItem.Food.Container.',
    properties: [
        new OA\Property(property: 'type', description: 'Container type identifier.', type: 'string', nullable: true),
        new OA\Property(property: 'closed', description: 'Whether the container starts closed (if provided).', type: 'boolean', nullable: true),
        new OA\Property(property: 'can_be_reclosed', description: 'Whether the container can be reclosed after opening.', type: 'boolean', nullable: true),
        new OA\Property(property: 'discard_when_consumed', description: 'Whether the container is discarded when fully consumed.', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'food_consumption',
    title: 'Food Consumption',
    description: 'Consumption settings from stdItem.Food.Consumption.',
    properties: [
        new OA\Property(property: 'volume', description: 'Consumption volume (unit as defined by game data).', type: 'double', example: 0.25, nullable: true),
        new OA\Property(property: 'one_shot_consume', description: 'Whether the item is consumed in one action.', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'food',
    title: 'Food',
    description: 'Consumable food or drink statistics sourced from stdItem.Food and description data.',
    properties: [
        new OA\Property(
            property: 'nutrition',
            ref: '#/components/schemas/food_nutrition',
            description: 'Dynamic nutrition totals keyed by nutrient name (snake_case).'
        ),
        new OA\Property(
            property: 'buffs',
            ref: '#/components/schemas/food_effect_durations',
            description: 'Dynamic map of buff type (snake_case) to duration in seconds.'
        ),
        new OA\Property(
            property: 'debuffs',
            ref: '#/components/schemas/food_effect_durations',
            description: 'Dynamic map of debuff type (snake_case) to duration in seconds.'
        ),

        new OA\Property(
            property: 'container',
            ref: '#/components/schemas/food_container',
            description: 'Container metadata.'
        ),
        new OA\Property(
            property: 'consumption',
            ref: '#/components/schemas/food_consumption',
            description: 'Consumption settings.'
        ),

        new OA\Property(property: 'nutritional_density_rating', description: 'Nutritional density rating (NDR) from food data or description.', type: 'string', nullable: true),
        new OA\Property(property: 'hydration_efficacy_index', description: 'Hydration efficacy index (HEI) from food data or description.', type: 'string', nullable: true),

        new OA\Property(
            property: 'container_type',
            description: 'Deprecated. Use `container.type`.',
            type: 'string',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'one_shot_consume',
            description: 'Deprecated. Use `consumption.one_shot_consume`.',
            type: 'boolean',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'can_be_reclosed',
            description: 'Deprecated. Use `container.can_be_reclosed`.',
            type: 'boolean',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'discard_when_consumed',
            description: 'Deprecated. Use `container.discard_when_consumed`.',
            type: 'boolean',
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'effects',
            description: 'List of effect strings (parsed from food data or description). Null when no effects are present.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true
        ),
    ],
    type: 'object'
)]
class FoodResource extends AbstractItemSpecificationResource
{
    protected string $type = 'Food';

    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $description = Arr::get($stdItem, 'DescriptionData', []);
        $food = Arr::get($stdItem, $this->type, []);

        $effects = Arr::get($food, 'Effects', []);

        if (is_string($effects)) {
            $effects = $this->splitEffects($effects);
        }

        if ($effects === [] || $effects === null) {
            $effects = $this->splitEffects(
                Arr::get($description, 'Effects', Arr::get($description, 'Effect', ''))
            );
        }

        return [
            'nutrition' => collect(Arr::get($food, 'Nutrition', []))->mapWithKeys(fn ($value, $key) => [
                Str::snake($key) => Arr::get($value, 'Total'),
            ]),

            'buffs' => collect(Arr::get($food, 'Buffs', []))->mapWithKeys(fn ($buff) => [
                Str::snake(Arr::get($buff, 'Type')) => Arr::get($buff, 'Duration', 0),
            ]),

            'debuffs' => collect(Arr::get($food, 'Debuffs', []))->mapWithKeys(fn ($buff) => [
                Str::snake(Arr::get($buff, 'Type')) => Arr::get($buff, 'Duration', 0),
            ]),

            'container' => [
                'type' => Arr::get($food, 'Container.Type'),
                'closed' => $this->toBool(Arr::get($food, 'Container.Closed')),
                'can_be_reclosed' => $this->toBool(Arr::get($food, 'Container.CanBeReclosed')),
                'discard_when_consumed' => $this->toBool(Arr::get($food, 'Container.DiscardWhenConsumed')),
            ],

            'consumption' => [
                'volume' => Arr::get($food, 'Consumption.Volume'),
                'one_shot_consume' => $this->toBool(Arr::get($food, 'Consumption.OneShotConsume')),
            ],

            'nutritional_density_rating' => Arr::get($food, 'NutritionalDensityRating', Arr::get($description, 'NDR')),
            'hydration_efficacy_index' => Arr::get($food, 'HydrationEfficacyIndex', Arr::get($description, 'HEI')),
            'container_type' => Arr::get($food, 'Container.Type'),
            'one_shot_consume' => $this->toBool(Arr::get($food, 'Consumption.OneShotConsume')),
            'can_be_reclosed' => $this->toBool(Arr::get($food, 'Container.CanBeReclosed')),
            'discard_when_consumed' => $this->toBool(Arr::get($food, 'Container.DiscardWhenConsumed')),
            'effects' => $effects === [] ? null : $effects,
        ];
    }

    private function splitEffects(string $effects): array
    {
        $effects = array_filter(array_map('trim', explode(',', $effects)));

        return array_values($effects);
    }

    private function toBool(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return null;
    }
}
