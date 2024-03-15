<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_port_type_v2',
    title: 'Item Port Compatible Types',
    properties: [
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(
            property: 'sub_types',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
    ],
    type: 'object'
)]
class ItemPortTypeResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'sub_types' => $this->subTypes->pluck('sub_type')->toArray(),
        ];
    }
}
