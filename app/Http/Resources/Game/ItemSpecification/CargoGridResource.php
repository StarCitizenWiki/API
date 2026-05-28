<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'cargo_grid',
    title: 'Cargo Grid',
    description: 'Cargo grid capacity and dimensions derived from inventory container data.',
    properties: [
        new OA\Property(property: 'scu', description: 'Cargo capacity in Standard Cargo Units.', type: 'double', nullable: true, x: ['suffix' => ' SCU']),
        new OA\Property(property: 'x', description: 'Interior width in meters.', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'y', description: 'Interior depth in meters.', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'z', description: 'Interior height in meters.', type: 'double', nullable: true, x: ['suffix' => ' m']),
    ],
    type: 'object'
)]
class CargoGridResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $container = Arr::get($stdItem, 'InventoryContainer', []);
        $grid = Arr::get($stdItem, 'CargoGrid', []);

        return [
            'scu' => Arr::get($container, 'SCU'),
            'x' => Arr::get($grid, 'Dimensions.X', Arr::get($container, 'x')),
            'y' => Arr::get($grid, 'Dimensions.Y', Arr::get($container, 'y')),
            'z' => Arr::get($grid, 'Dimensions.Z', Arr::get($container, 'z')),
        ];
    }
}
