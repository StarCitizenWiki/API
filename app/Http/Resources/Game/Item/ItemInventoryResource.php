<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_inventory',
    title: 'Item Inventory',
    description: 'Container (Inventory) of an item, resolved from SCItemInventoryContainerComponentParams',
    properties: [
        new OA\Property(property: 'uuid', description: 'Value of containerParams', type: 'string', nullable: true),
        new OA\Property(property: 'width', description: 'interiorDimensions@x', type: 'double', nullable: true),
        new OA\Property(property: 'height', description: 'interiorDimensions@z', type: 'double', nullable: true),
        new OA\Property(property: 'length', description: 'interiorDimensions@y', type: 'double', nullable: true),
        new OA\Property(property: 'volume', description: 'x*y*z', type: 'double', nullable: true),
        new OA\Property(
            property: 'scu',
            description: 'Amount of SCU this container can hold. This is the raw value as set in the game data.',
            type: 'double',
            example: 0.002,
            nullable: true
        ),
        new OA\Property(
            property: 'scu_converted',
            description: 'Raw SCU value (for example µSCU) converted to SCU',
            type: 'double',
            nullable: true
        ),
        new OA\Property(property: 'unit', description: 'Unit as shown in the UI for example µSCU', type: 'string', nullable: true),
        new OA\Property(
            property: 'micro_scu',
            description: 'µSCU version of SCU. Only calculated when unit is 0.',
            type: 'double',
            nullable: true
        ),
        new OA\Property(property: 'open', description: 'IsOpenContainer', type: 'boolean', nullable: true),
        new OA\Property(property: 'external', description: 'IsExternalContainer', type: 'boolean', nullable: true),
        new OA\Property(property: 'closed', description: 'IsClosedContainer', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
/** @param array $resource Raw inventory container data from stdItem.InventoryContainer sub-array */
class ItemInventoryResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => Arr::get($this, 'UUID'),
            'width' => Arr::get($this, 'X'),
            'height' => Arr::get($this, 'Z'),
            'length' => Arr::get($this, 'Y'),
            'volume' => Arr::has($this, ['X', 'Z', 'Y'])
                ? Arr::get($this, 'X') * Arr::get($this, 'Z') * Arr::get($this, 'Y')
                : null,
            'scu' => Arr::get($this, 'SCU'),
            'scu_converted' => Arr::has($this, ['SCU', 'Unit'])
                ? Arr::get($this, 'SCU') * (10 ** Arr::get($this, 'Unit'))
                : null,
            'unit' => Arr::get($this, 'UnitName'),
            $this->mergeWhen(Arr::get($this, 'Unit') === 0, fn () => [
                'micro_scu' => Arr::get($this, 'SCU') * (10 ** 6),
            ]),
            'open' => Arr::get($this, 'IsOpenContainer'),
            'external' => Arr::get($this, 'IsExternalContainer'),
            'closed' => Arr::get($this, 'IsClosedContainer'),
        ];
    }
}
