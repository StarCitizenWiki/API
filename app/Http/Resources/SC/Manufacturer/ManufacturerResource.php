<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Manufacturer;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Vehicle\VehicleLinkResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manufacturer_v2',
    title: 'Manufacturer',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(
            property: 'ships',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_link_v2')
        ),
        new OA\Property(
            property: 'vehicles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_link_v2')
        ),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/item_link_v2')
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

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'uuid' => $this->uuid,
            'ships' => VehicleLinkResource::collection($this->ships()),
            'vehicles' => VehicleLinkResource::collection($this->groundVehicles()),
            'items' => ItemLinkResource::collection($this->items()),
        ];
    }
}
