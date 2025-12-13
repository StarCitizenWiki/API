<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_port_type',
    title: 'Item Port Compatible Types',
    properties: [
        new OA\Property(property: 'type', type: 'string', nullable: true),
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
    public function toArray(Request $request): array
    {
        return [
            'type' => Arr::get($this, 'Type'),
            'sub_types' => Arr::get($this, 'SubTypes', []),
        ];
    }
}
