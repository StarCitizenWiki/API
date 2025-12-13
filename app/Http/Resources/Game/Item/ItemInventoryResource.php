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
        new OA\Property(property: 'dimension', description: 'x*y*z', type: 'double', nullable: true),
        new OA\Property(
            property: 'scu',
            description: 'Amount of SCU this container can hold.',
            type: 'double',
            example: 0.002,
            nullable: true
        ),
        new OA\Property(
            property: 'scu_converted',
            description: 'SCU converted to referenced unit, e.g. 2000 (µSCU)',
            type: 'double',
            nullable: true
        ),
        new OA\Property(property: 'unit', description: 'Unit as shown in the UI for example µSCU', type: 'string', nullable: true),
        new OA\Property(property: 'open', description: 'IsOpenContainer', type: 'boolean', nullable: true),
        new OA\Property(property: 'external', description: 'IsExternalContainer', type: 'boolean', nullable: true),
        new OA\Property(property: 'closed', description: 'IsClosedContainer', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
class ItemInventoryResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => Arr::get($this, 'UUID'),
            'width' => Arr::get($this, 'x'),
            'height' => Arr::get($this, 'z'),
            'length' => Arr::get($this, 'y'),
            'dimension' => Arr::has($this, ['x', 'z', 'y'])
                ? Arr::get($this, 'x') * Arr::get($this, 'z') * Arr::get($this, 'y')
                : null,
            'scu' => Arr::get($this, 'SCU'),
            'scu_converted' => Arr::has($this, ['SCU', 'Unit'])
                ? Arr::get($this, 'SCU') * (10 ** Arr::get($this, 'Unit'))
                : null,
            'unit' => Arr::get($this, 'UnitName'),
            'open' => Arr::get($this, 'IsOpenContainer'),
            'external' => Arr::get($this, 'IsExternalContainer'),
            'closed' => Arr::get($this, 'IsClosedContainer'),
        ];
    }
}
