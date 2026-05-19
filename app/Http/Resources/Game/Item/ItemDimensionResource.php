<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_dimension',
    title: 'Item Dimension',
    description: 'Dimensions of an item, sourced from InventoryOccupancy in the item definition.',
    properties: [
        // Deprecated
        new OA\Property(
            property: 'width',
            description: 'Deprecated: Use dimensions, cargo_dimension, or ui_dimension instead. Width in meters, either from UIDimensions or Dimensions.',
            type: 'double',
            example: 0.52,
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'height',
            description: 'Deprecated: Use dimensions, cargo_dimension, or ui_dimension instead. Height in meters, either from UIDimensions or Dimensions.',
            type: 'double',
            example: 0.18,
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'length',
            description: 'Deprecated: Use dimensions, cargo_dimension, or ui_dimension instead. Length in meters, either from UIDimensions or Dimensions.',
            type: 'double',
            example: 1.12,
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'volume',
            description: 'Cubic volume derived from the dimensions (SCU).',
            type: 'double',
            example: 0.11,
            nullable: true,
        ),
        new OA\Property(
            property: 'volume_converted',
            description: 'Converted volume value from game data (e.g. in µSCU).',
            type: 'number',
            nullable: true,
        ),
        new OA\Property(
            property: 'volume_converted_unit',
            description: 'Unit of the converted volume (e.g. "µSCU", "SCU").',
            type: 'string',
            nullable: true,
        ),

        // Deprecated conditional block
        new OA\Property(
            property: 'true_dimension',
            description: 'Deprecated: Use dimensions instead. True 3D model dimensions in meters. Only present when UI values differ from the true dimensions.',
            properties: [
                new OA\Property(property: 'width', type: 'double', nullable: true),
                new OA\Property(property: 'height', type: 'double', nullable: true),
                new OA\Property(property: 'length', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true,
            deprecated: true,
        ),

        // New dimension blocks
        new OA\Property(
            property: 'dimensions',
            ref: '#/components/schemas/item_dimension_block',
            description: 'True 3D model bounding box dimensions in meters (from InventoryOccupancy.Dimensions).',
            nullable: true,
        ),
        new OA\Property(
            property: 'cargo_dimension',
            ref: '#/components/schemas/item_dimension_block',
            description: 'Cargo grid occupancy dimensions in meters (from InventoryOccupancy.CargoGrid).',
            nullable: true,
        ),
        new OA\Property(
            property: 'ui_dimension',
            ref: '#/components/schemas/item_dimension_block',
            description: 'UI display dimensions shown in the in-game inventory in meters (from InventoryOccupancy.UIDimensions).',
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'item_dimension_block',
    title: 'Item Dimension Block',
    description: 'A dimension block with width, height, and length in meters.',
    properties: [
        new OA\Property(property: 'width', description: 'Width in meters.', type: 'double', example: 0.52),
        new OA\Property(property: 'height', description: 'Height in meters.', type: 'double', example: 0.18),
        new OA\Property(property: 'length', description: 'Length in meters.', type: 'double', example: 1.12),
    ],
    type: 'object',
)]
class ItemDimensionResource extends AbstractBaseResource
{
    use ExtractsJsonData;

    public function toArray(Request $request): array
    {
        $dimensions = $this->extractFromStdItem($this->resource, 'InventoryOccupancy.Dimensions');
        $cargoGrid = $this->extractFromStdItem($this->resource, 'InventoryOccupancy.CargoGrid');
        $uiDimensions = $this->extractFromStdItem($this->resource, 'InventoryOccupancy.UIDimensions');

        $sumDim = Arr::get($dimensions, 'Width', 0) + Arr::get($dimensions, 'Height', 0) + Arr::get($dimensions, 'Length', 0);
        $sumUiDim = Arr::get($uiDimensions, 'Width', 0) + Arr::get($uiDimensions, 'Height', 0) + Arr::get($uiDimensions, 'Length', 0);

        // Legacy: prefer UIDimensions when they differ from true Dimensions
        $dim = $uiDimensions && $sumDim !== $sumUiDim ? $uiDimensions : $dimensions;

        return [
            // Deprecated flat fields (backwards compat)
            'width' => Arr::get($dim, 'Width'),
            'height' => Arr::get($dim, 'Height'),
            'length' => Arr::get($dim, 'Length'),

            'volume' => $this->extractFromStdItem($this->resource, 'InventoryOccupancy.Volume.SCU'),
            'volume_converted' => $this->extractFromStdItem($this->resource, 'InventoryOccupancy.Volume.SCUConverted'),
            'volume_converted_unit' => $this->extractFromStdItem($this->resource, 'InventoryOccupancy.Volume.Unit'),

            // Deprecated conditional block (backwards compat)
            $this->mergeWhen($uiDimensions && $sumDim !== $sumUiDim, [
                'true_dimension' => $this->formatBlock($dimensions),
            ]),

            // New dimension blocks
            $this->mergeWhen($dimensions !== null, [
                'dimensions' => $this->formatBlock($dimensions),
            ]),
            $this->mergeWhen($cargoGrid !== null, [
                'cargo_dimension' => $this->formatBlock($cargoGrid),
            ]),
            $this->mergeWhen($uiDimensions !== null, [
                'ui_dimension' => $this->formatBlock($uiDimensions),
            ]),
        ];
    }

    /**
     * @return array{width: float|null, height: float|null, length: float|null}
     */
    private function formatBlock(mixed $block): array
    {
        return [
            'width' => Arr::get($block, 'Width'),
            'height' => Arr::get($block, 'Height'),
            'length' => Arr::get($block, 'Length'),
        ];
    }
}
