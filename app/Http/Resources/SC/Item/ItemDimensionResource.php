<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_dimension_v2',
    title: 'Item Dimension',
    description: 'Dimensions of an item as shown in the ui',
    properties: [
        new OA\Property(property: 'width', type: 'double'),
        new OA\Property(property: 'height', type: 'double'),
        new OA\Property(property: 'length', type: 'double'),
        new OA\Property(property: 'volume', type: 'double'),
        new OA\Property(
            property: 'true_dimension',
            description: 'These are the "true" dimensions of the item',
            properties: [
                new OA\Property(property: 'width', type: 'double'),
                new OA\Property(property: 'height', type: 'double'),
                new OA\Property(property: 'length', type: 'double'),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class ItemDimensionResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $dim = $this->dimension;
        $trueDim = $this->true_dimension;
        $sumDim = $dim->width + $dim->height + $dim->length;
        $sumTrueDim = $trueDim->width + $trueDim->height + $trueDim->length;

        return [
            'width' => $dim->width,
            'height' => $dim->height,
            'length' => $dim->length,
            'volume' => $dim->volume ?? $trueDim->volume,
            $this->mergeWhen($sumDim !== $sumTrueDim, [
                'true_dimension' => [
                    'width' => $trueDim->width,
                    'height' => $trueDim->height,
                    'length' => $trueDim->length,
                ]
            ]),
        ];
    }
}
