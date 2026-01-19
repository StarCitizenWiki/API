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
    description: 'Dimensions of an item, generated from inventoryOccupancyDimensions or inventoryOccupancyDimensionsUIOverride in AttachDef.',
    properties: [
        new OA\Property(
            property: 'width',
            description: 'Width in meters, either from "DimensionOverrides" or "Dimension".',
            type: 'double',
            example: 0.52,
            nullable: true
        ),
        new OA\Property(
            property: 'height',
            description: 'Height in meters, either from "DimensionOverrides" or "Dimension".',
            type: 'double',
            example: 0.18,
            nullable: true
        ),
        new OA\Property(
            property: 'length',
            description: 'Length in meters, either from "DimensionOverrides" or "Dimension".',
            type: 'double',
            example: 1.12,
            nullable: true
        ),
        new OA\Property(
            property: 'volume',
            description: 'Cubic volume derived from the true dimensions (SCU).',
            type: 'double',
            example: 0.11,
            nullable: true
        ),
        new OA\Property(
            property: 'true_dimension',
            description: 'Raw dimensions from the game data in meters prior to overrides. Added only when the UI values differ from the canonical measurements.',
            properties: [
                new OA\Property(
                    property: 'width',
                    description: 'Original width pulled directly from the unmodified item definition (meters).',
                    type: 'double',
                    example: 0.5,
                    nullable: true
                ),
                new OA\Property(
                    property: 'height',
                    description: 'Original height pulled directly from the unmodified item definition (meters).',
                    type: 'double',
                    example: 0.2,
                    nullable: true
                ),
                new OA\Property(
                    property: 'length',
                    description: 'Original length pulled directly from the unmodified item definition (meters).',
                    type: 'double',
                    example: 1.1,
                    nullable: true
                ),
            ],
            type: 'object',
            example: [
                'width' => 0.5,
                'height' => 0.2,
                'length' => 1.1,
            ],
            nullable: true
        ),
    ],
    type: 'object'
)]
class ItemDimensionResource extends AbstractBaseResource
{
    use ExtractsJsonData;

    public function toArray(Request $request): array
    {
        $dimensions = $this->extractFromStdItem($this->resource, 'InventoryOccupancy.Dimensions');
        $uiDimensions = $this->extractFromStdItem($this->resource, 'InventoryOccupancy.UIDimensions');

        $sumDim = Arr::get($dimensions, 'Width', 0) + Arr::get($dimensions, 'Height', 0) + Arr::get($dimensions, 'Length', 0);
        $sumTrueDim = Arr::get($uiDimensions, 'Width', 0) + Arr::get($uiDimensions, 'Height', 0) + Arr::get($uiDimensions, 'Length', 0);

        $dim = $uiDimensions && $sumDim !== $sumTrueDim ? $uiDimensions : $dimensions;

        return [
            'width' => Arr::get($dim, 'Width'),
            'height' => Arr::get($dim, 'Height'),
            'length' => Arr::get($dim, 'Length'),
            'volume' => $this->extractFromStdItem($this->resource, 'InventoryOccupancy.Volume.SCU'),
            'volume_converted' => $this->extractFromStdItem($this->resource, 'InventoryOccupancy.Volume.SCUConverted'),
            'volume_converted_unit' => $this->extractFromStdItem($this->resource, 'InventoryOccupancy.Volume.Unit'),
            $this->mergeWhen($uiDimensions && $sumDim !== $sumTrueDim, [
                'true_dimension' => [
                    'width' => Arr::get($dimensions, 'Width'),
                    'height' => Arr::get($dimensions, 'Height'),
                    'length' => Arr::get($dimensions, 'Length'),
                ],
            ]),
        ];
    }
}
