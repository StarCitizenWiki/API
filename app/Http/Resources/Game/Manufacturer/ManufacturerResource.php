<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Manufacturer;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manufacturer',
    title: 'In game manufacturer',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(
            property: 'ships',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_link')
        ),
        new OA\Property(
            property: 'vehicles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_link')
        ),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/item_link')
        ),
    ],
    type: 'object'
)]
class ManufacturerResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'ships',
            'vehicles',
            'items',
        ];
    }

    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'uuid' => $this->uuid,
            //            'ships' => VehicleLinkResource::collection($this->ships()),
            //            'vehicles' => VehicleLinkResource::collection($this->groundVehicles()),
            //            'items' => ItemLinkResource::collection($this->items()),
        ];
    }
}
