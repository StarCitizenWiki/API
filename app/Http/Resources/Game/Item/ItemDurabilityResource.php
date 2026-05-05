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
    description: 'Durability information of an item, generated from SHealthComponentParams (health, repairable, salvageable) and SWearAccumulatorParams (max_lifetime).',
    properties: [
        new OA\Property(property: 'health', description: 'SHealthComponentParams@Health', type: 'double', nullable: true),
        new OA\Property(property: 'max_lifetime', description: 'MaxLifetimeHours attribute on SWearAccumulatorParams', type: 'double', nullable: true),
        new OA\Property(property: 'repairable', description: 'SHealthComponentParams@IsRepairable', type: 'boolean'),
        new OA\Property(property: 'salvageable', description: 'SHealthComponentParams@IsSalvagable', type: 'boolean'),
        new OA\Property(property: 'resistance', description: 'Resistance attributes on SHealthComponentParams', type: 'object', nullable: true),
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
            'max_lifetime' => Arr::get($this, 'Lifetime'),
            'lifetime' => Arr::get($this, 'Lifetime'),
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
