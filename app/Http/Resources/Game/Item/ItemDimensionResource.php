<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
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
    public function toArray(Request $request): array
    {
        $stdItem = collect($this->data->get('stdItem'));

        $trueDim = $stdItem->get('Dimension', []);
        $dim = $stdItem->has('DimensionOverrides') ? $stdItem->get('DimensionOverrides') : $trueDim;

        $sumDim = Arr::get($dim, 'Width', 0) + Arr::get($dim, 'Height', 0) + Arr::get($dim, 'Length', 0);
        $sumTrueDim = Arr::get($trueDim, 'Width', 0) + Arr::get($trueDim, 'Height', 0) + Arr::get($trueDim, 'Length', 0);

        return [
            'width' => Arr::get($dim, 'Width'),
            'height' => Arr::get($dim, 'Height'),
            'length' => Arr::get($dim, 'Length'),
            'volume' => Arr::get($trueDim, 'Volume'),
            $this->mergeWhen($sumDim !== $sumTrueDim, [
                'true_dimension' => [
                    'width' => Arr::get($trueDim, 'Width'),
                    'height' => Arr::get($trueDim, 'Height'),
                    'length' => Arr::get($trueDim, 'Length'),
                ],
            ]),
        ];
    }
}
