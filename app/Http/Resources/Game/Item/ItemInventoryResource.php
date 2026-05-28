<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\ItemData;
use App\Support\ScuBox;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_inventory',
    title: 'Item Inventory',
    description: 'Container (Inventory) of an item, resolved from SCItemInventoryContainerComponentParams',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique identifier of the referenced InventoryContainer.', type: 'string', nullable: true),
        new OA\Property(property: 'width', description: 'Interior width in meters (interiorDimensions.x).', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'height', description: 'Interior height in meters (interiorDimensions.z).', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'length', description: 'Interior depth in meters (interiorDimensions.y).', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'volume', description: 'Interior volume in cubic meters (width * height * length).', type: 'double', nullable: true, x: ['suffix' => ' m³']),
        new OA\Property(
            property: 'scu',
            description: 'Raw SCU capacity as set in game data.',
            type: 'double',
            example: 0.002,
            nullable: true,
            x: ['suffix' => ' SCU']
        ),
        new OA\Property(
            property: 'scu_converted',
            description: 'SCU capacity converted to the unit specified in the unit field (e.g. µSCU, cSCU, or SCU).',
            type: 'double',
            nullable: true,
            x: ['tabulator-formatter' => 'volumeWithUnit', 'formatter-params' => ['unitField' => 'inventory.unit']]
        ),
        new OA\Property(property: 'unit', description: 'Unit label displayed in the UI, e.g. "µSCU" or "SCU".', type: 'string', nullable: true),
        new OA\Property(
            property: 'micro_scu',
            description: 'Capacity in µSCU. Only present when unit exponent is 0 (standard SCU).',
            type: 'double',
            nullable: true,
            x: ['suffix' => ' µSCU']
        ),
        new OA\Property(property: 'open', description: 'Whether this is an open container (e.g. a cargo grid).', type: 'boolean', nullable: true),
        new OA\Property(property: 'external', description: 'Whether this container is externally accessible (e.g. a cargo pod).', type: 'boolean', nullable: true),
        new OA\Property(property: 'closed', description: 'Whether this is a closed container (e.g. a locker or personal storage).', type: 'boolean', nullable: true),
        new OA\Property(property: 'min_size', description: 'Minimum item dimensions accepted by this container in meters.', properties: [
            new OA\Property(property: 'x', description: 'Width in meters.', type: 'number', nullable: true, x: ['suffix' => ' m']),
            new OA\Property(property: 'y', description: 'Depth in meters.', type: 'number', nullable: true, x: ['suffix' => ' m']),
            new OA\Property(property: 'z', description: 'Height in meters.', type: 'number', nullable: true, x: ['suffix' => ' m']),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'max_size', description: 'Maximum item dimensions accepted by this container in meters.', properties: [
            new OA\Property(property: 'x', description: 'Width in meters.', type: 'number', nullable: true, x: ['suffix' => ' m']),
            new OA\Property(property: 'y', description: 'Depth in meters.', type: 'number', nullable: true, x: ['suffix' => ' m']),
            new OA\Property(property: 'z', description: 'Height in meters.', type: 'number', nullable: true, x: ['suffix' => ' m']),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'min_scu_box', description: 'Smallest standard SCU box whose dimensions satisfy the min item size. Powers of two: 0.125, 1, 2, 4, 8, 16, 32.', type: 'number', example: 1, nullable: true, x: ['suffix' => ' SCU']),
        new OA\Property(property: 'max_scu_box', description: 'Largest standard SCU box that fits within the max item size and interior dimensions. Powers of two: 0.125, 1, 2, 4, 8, 16, 32.', type: 'number', example: 8, nullable: true, x: ['suffix' => ' SCU']),
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
                ? round(Arr::get($container, 'X') * Arr::get($container, 'Z') * Arr::get($container, 'Y'), 4)
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
            $this->mergeWhen(Arr::has($container, 'MinSize'), function () use ($container) {
                $minSize = $this->formatSizeBlock(Arr::get($container, 'MinSize'));
                $minScuBox = $minSize !== null ? ScuBox::smallestThatFits($minSize) : null;

                return [
                    'min_size' => $minSize,
                    ...($minScuBox !== null && $minScuBox !== 1 ? ['min_scu_box' => $minScuBox] : []),
                ];
            }),
            $this->mergeWhen(Arr::has($container, 'MaxSize'), function () use ($container) {
                $maxSize = $this->formatSizeBlock(Arr::get($container, 'MaxSize'));

                $interior = Arr::has($container, ['X', 'Y', 'Z'])
                    ? ['x' => Arr::get($container, 'X'), 'y' => Arr::get($container, 'Y'), 'z' => Arr::get($container, 'Z')]
                    : null;

                $maxScuBox = match (true) {
                    $maxSize === null => null,
                    $interior !== null => ScuBox::largestThatFitsInGrid($interior, $maxSize),
                    default => ScuBox::largestThatFits($maxSize),
                };

                return [
                    'max_size' => $maxSize,
                    'max_scu_box' => $maxScuBox,
                ];
            }),
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
