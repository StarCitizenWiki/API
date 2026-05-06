<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\ItemData;
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
        new OA\Property(property: 'min_size', description: 'Minimum item dimensions accepted by this container.', properties: [
            new OA\Property(property: 'x', type: 'number', nullable: true),
            new OA\Property(property: 'y', type: 'number', nullable: true),
            new OA\Property(property: 'z', type: 'number', nullable: true),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'max_size', description: 'Maximum item dimensions accepted by this container.', properties: [
            new OA\Property(property: 'x', type: 'number', nullable: true),
            new OA\Property(property: 'y', type: 'number', nullable: true),
            new OA\Property(property: 'z', type: 'number', nullable: true),
        ], type: 'object', nullable: true),
    ],
    type: 'object'
)]
/** @param array|ItemData $resource Raw inventory container data or an ItemData model */
class ItemInventoryResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof ItemData) {
            $data = $this->resource->data;
            $container = Arr::get($data, 'Item.stdItem.InventoryContainer')
                ?? Arr::get($data, 'stdItem.InventoryContainer')
                ?? [];

            return $this->formatContainer($container);
        }

        return $this->formatContainer($this->resource instanceof \ArrayAccess
            ? $this->resource->toArray()
            : (array) $this->resource);
    }

    private function formatContainer(array $container): array
    {
        return [
            'uuid' => Arr::get($container, 'UUID'),
            'width' => Arr::get($container, 'X'),
            'height' => Arr::get($container, 'Z'),
            'length' => Arr::get($container, 'Y'),
            'volume' => Arr::has($container, ['X', 'Z', 'Y'])
                ? Arr::get($container, 'X') * Arr::get($container, 'Z') * Arr::get($container, 'Y')
                : null,
            'scu' => Arr::get($container, 'SCU'),
            'scu_converted' => Arr::has($container, ['SCU', 'Unit'])
                ? Arr::get($container, 'SCU') * (10 ** Arr::get($container, 'Unit'))
                : null,
            'unit' => Arr::get($container, 'UnitName'),
            $this->mergeWhen(Arr::get($container, 'Unit') === 0, fn () => [
                'micro_scu' => Arr::get($container, 'SCU') * (10 ** 6),
            ]),
            'open' => Arr::get($container, 'IsOpenContainer'),
            'external' => Arr::get($container, 'IsExternalContainer'),
            'closed' => Arr::get($container, 'IsClosedContainer'),
            $this->mergeWhen(Arr::has($container, 'MinSize'), fn () => [
                'min_size' => $this->formatSizeBlock(Arr::get($container, 'MinSize')),
            ]),
            $this->mergeWhen(Arr::has($container, 'MaxSize'), fn () => [
                'max_size' => $this->formatSizeBlock(Arr::get($container, 'MaxSize')),
            ]),
        ];
    }

    /**
     * @return array{x: float|int, y: float|int, z: float|int}|null
     */
    private function formatSizeBlock(mixed $block): ?array
    {
        if (! is_array($block)) {
            return null;
        }

        $x = Arr::get($block, 'X', Arr::get($block, 'x'));
        $y = Arr::get($block, 'Y', Arr::get($block, 'y'));
        $z = Arr::get($block, 'Z', Arr::get($block, 'z'));

        if ($x === null || $y === null || $z === null) {
            return null;
        }

        return ['x' => $x, 'y' => $y, 'z' => $z];
    }
}
