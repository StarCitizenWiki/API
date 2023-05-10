<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_container_v2',
    title: 'Item Container',
    description: 'Container (Inventory) of an item',
    properties: [
        new OA\Property(property: 'width', type: 'double'),
        new OA\Property(property: 'height', type: 'double'),
        new OA\Property(property: 'length', type: 'double'),
        new OA\Property(property: 'dimension', type: 'double'),
        new OA\Property(property: 'scu', type: 'double'),
        new OA\Property(property: 'scu_converted', type: 'double'),
        new OA\Property(property: 'unit', type: 'string'),
    ],
    type: 'object'
)]
class ItemContainerResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $unit = match ($this->unit) {
            2 => 'cSCU',
            6 => 'µSCU',
            default => 'SCU',
        };

        return [
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
            'dimension' => $this->dimension,
            'scu' => $this->calculated_scu,
            'scu_converted' => $this->original_converted_scu,
            'unit' => $unit,
        ];
    }
}
