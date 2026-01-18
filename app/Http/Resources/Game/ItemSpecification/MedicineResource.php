<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'medicine',
    title: 'Medicine',
    description: 'Medical consumable statistics sourced from stdItem.Medical, reusing the shared container/consumption/nutrition structure.',
    properties: [
        new OA\Property(
            property: 'nutrition',
            description: 'Dynamic map of nutrient name (snake_case) to total amount.',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'double')
        ),
        new OA\Property(
            property: 'debuffs',
            description: 'Dynamic map of debuff type (snake_case) to duration in seconds.',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'double')
        ),

        new OA\Property(
            property: 'container',
            description: 'Container metadata from stdItem.Medical.Container.',
            properties: [
                new OA\Property(property: 'type', description: 'Container type identifier.', type: 'string', nullable: true),
                new OA\Property(property: 'closed', description: 'Whether the container starts closed (if provided).', type: 'boolean', nullable: true),
                new OA\Property(property: 'can_be_reclosed', description: 'Whether the container can be reclosed after opening.', type: 'boolean', nullable: true),
                new OA\Property(property: 'discard_when_consumed', description: 'Whether the container is discarded when fully consumed.', type: 'boolean', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'consumption',
            description: 'Consumption settings from stdItem.Medical.Consumption.',
            properties: [
                new OA\Property(property: 'volume', description: 'Consumption volume (unit as defined by game data).', type: 'double', example: 0.25, nullable: true),
                new OA\Property(property: 'one_shot_consume', description: 'Whether the item is consumed in one action.', type: 'boolean', nullable: true),
            ],
            type: 'object'
        ),

        new OA\Property(
            property: 'combat_buffs',
            description: 'Dynamic map of combat buff flags (derived from MedicalEffects.CombatBuffs). Keys are normalized (snake_case, without `_mask`). Values are boolean flags.',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'boolean')
        ),
        new OA\Property(
            property: 'impact_resistances',
            description: 'Dynamic map of impact resistance flags (derived from MedicalEffects.ImpactResistance). Keys are normalized (snake_case, without `impact_resistance_` or `_mask`). Values are boolean flags.',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'boolean')
        ),
    ],
    type: 'object'
)]
class MedicineResource extends FoodResource
{
    protected string $type = 'Medical';

    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $medicine = Arr::get($stdItem, 'Medical', []);

        $combatBuffs = collect(Arr::get($medicine, 'MedicalEffects.CombatBuffs', []))->toArray();
        $impactResistance = Arr::get($medicine, 'MedicalEffects.ImpactResistance', []);

        $return = [
            ...parent::toArray($request),
            'combat_buffs' => collect(Arr::get($medicine, 'Buffs', []))
                ->reject(fn ($value) => ! in_array($value['Type'], $combatBuffs, true))
                ->mapWithKeys(fn ($value) => [
                    str_replace(['_mask'], '', Str::snake($value['Type'])) => true,
                ])->toArray(),
            'impact_resistances' => collect(Arr::get($medicine, 'Buffs', []))
                ->reject(fn ($value) => ! in_array($value['Type'], $impactResistance, true))
                ->mapWithKeys(fn ($value) => [
                    str_replace(['impact_resistance_', '_mask'], '', Str::snake($value['Type'])) => true,
                ])
                ->toArray(),
        ];

        unset(
            $return['buffs'],
            $return['nutritional_density_rating'],
            $return['hydration_efficacy_index'],
            $return['container_type'],
            $return['one_shot_consume'],
            $return['can_be_reclosed'],
            $return['discard_when_consumed'],
            $return['effects'],
        );

        return $return;
    }
}
