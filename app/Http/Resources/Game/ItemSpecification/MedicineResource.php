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
    properties: [

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
                    str_replace(['_mask'], '', Str::snake($value['Type'])) => true, // (bool)($value['Duration'] ?? 0) // TODO: this is always null in the source data
                ])->toArray(),
            'impact_resistances' => collect(Arr::get($medicine, 'Buffs', []))
                ->reject(fn ($value) => ! in_array($value['Type'], $impactResistance, true))
                ->mapWithKeys(fn ($value) => [
                    str_replace(['impact_resistance_', '_mask'], '', Str::snake($value['Type'])) => true, // (bool)($value['Duration'] ?? 0) // TODO: this is always null in the source data
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
