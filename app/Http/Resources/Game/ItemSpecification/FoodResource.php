<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'food',
    title: 'Food',
    description: 'Consumable food or drink statistics sourced from stdItem.Food and description data.',
    properties: [
        new OA\Property(property: 'nutritional_density_rating', type: 'string', nullable: true),
        new OA\Property(property: 'hydration_efficacy_index', type: 'string', nullable: true),
        new OA\Property(property: 'effects', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'container_type', type: 'string', nullable: true),
        new OA\Property(property: 'one_shot_consume', type: 'boolean', nullable: true),
        new OA\Property(property: 'can_be_reclosed', type: 'boolean', nullable: true),
        new OA\Property(property: 'discard_when_consumed', type: 'boolean', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
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
