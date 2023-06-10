<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_description_data_v2',
    title: 'Item Description Data',
    description: 'Data found in the description of an item, e.g. "Carrying Capacity: 2kµSCU".',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'value', type: 'string'),
    ],
    type: 'object'
)]
class ItemDescriptionDataResource extends AbstractBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'type' => $this->value,
        ];
    }
}
