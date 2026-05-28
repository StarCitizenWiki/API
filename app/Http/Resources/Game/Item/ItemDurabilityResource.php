<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_durability',
    title: 'Item Durability',
    description: 'Durability information of an item, generated from SHealthComponentParams (health, repairable, salvageable) and SDegradationParams (lifetime).',
    properties: [
        new OA\Property(property: 'health', description: 'Hit points of the item from SHealthComponentParams.', type: 'double', nullable: true, x: ['suffix' => ' HP']),
        new OA\Property(property: 'lifetime', description: 'Wear lifetime in hours from SDegradationParams.MaxLifetimeHours.', type: 'double', nullable: true, x: ['suffix' => ' h']),
        new OA\Property(property: 'max_lifetime', description: 'Deprecated: Use lifetime.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'repairable', description: 'Whether the item can be repaired.', type: 'boolean'),
        new OA\Property(property: 'salvageable', description: 'Whether the item can be salvaged.', type: 'boolean'),
        new OA\Property(
            property: 'resistance',
            description: 'Damage resistance multipliers per type (Physical, Energy, Distortion, Thermal, Biochemical, Stun). The multiplier scales incoming damage: 1.0 = full damage, <1.0 = reduced damage, 0 = immune, >1.0 = increased damage.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical damage multiplier. Incoming damage is scaled by this value.', type: 'double', nullable: true, x: ['suffix' => ' ×']),
                new OA\Property(property: 'energy', description: 'Energy damage multiplier. Incoming damage is scaled by this value.', type: 'double', nullable: true, x: ['suffix' => ' ×']),
                new OA\Property(property: 'distortion', description: 'Distortion damage multiplier. Incoming damage is scaled by this value.', type: 'double', nullable: true, x: ['suffix' => ' ×']),
                new OA\Property(property: 'thermal', description: 'Thermal damage multiplier. Incoming damage is scaled by this value.', type: 'double', nullable: true, x: ['suffix' => ' ×']),
                new OA\Property(property: 'biochemical', description: 'Biochemical damage multiplier. Incoming damage is scaled by this value.', type: 'double', nullable: true, x: ['suffix' => ' ×']),
                new OA\Property(property: 'stun', description: 'Stun damage multiplier. Incoming damage is scaled by this value.', type: 'double', nullable: true, x: ['suffix' => ' ×']),
            ],
            type: 'object',
            nullable: true,
        ),
    ],
    type: 'object'
)]
/** @param array $resource Raw durability data from stdItem.Durability sub-array */
class ItemDurabilityResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'health' => Arr::get($this, 'Health'),
            'lifetime' => Arr::get($this, 'Lifetime'),
            'max_lifetime' => Arr::get($this, 'Lifetime'),  // deprecated: use lifetime
            'repairable' => Arr::get($this, 'Repairable') === 1,
            'salvageable' => Arr::get($this, 'Salvageable') === 1,
            'resistance' => collect(Arr::get($this, 'Resistance', []))
                ->mapWithKeys(fn ($value, $key) => [
                    strtolower($key) => Arr::get($value, 'Multiplier'),
                ])
                ->toArray(),
        ];
    }
}
