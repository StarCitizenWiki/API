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
    public function toArray($request): array
    {
        $sumDim = $this->dimension->width + $this->dimension->height + $this->dimension->length;
        $sumTrueDim = $this->true_dimension->width + $this->true_dimension->height + $this->true_dimension->length;

        return [
            'width' => $this->dimension->width,
            'height' => $this->dimension->height,
            'length' => $this->dimension->length,
            'volume' => $this->dimension->volume ?? $this->true_dimension->volume,
            $this->mergeWhen($sumDim !== $sumTrueDim, [
                'true_dimension' => [
                    'width' => $this->true_dimension->width,
                    'height' => $this->true_dimension->height,
                    'length' => $this->true_dimension->length,
                ]
            ]),
        ];
    }
}
