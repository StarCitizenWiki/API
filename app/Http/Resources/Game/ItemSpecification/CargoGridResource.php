<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'cargo_grid',
    title: 'Cargo Grid',
    description: 'Cargo grid dimensions derived from inventory container data.',
    properties: [
        new OA\Property(property: 'x', type: 'double', nullable: true),
        new OA\Property(property: 'y', type: 'double', nullable: true),
        new OA\Property(property: 'z', type: 'double', nullable: true),
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

        return array_filter([
            'x' => Arr::get($grid, 'Dimensions.X', Arr::get($container, 'x')),
            'y' => Arr::get($grid, 'Dimensions.Y', Arr::get($container, 'y')),
            'z' => Arr::get($grid, 'Dimensions.Z', Arr::get($container, 'z')),
        ], static fn ($value) => $value !== null);
    }
}
