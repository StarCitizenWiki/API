<?php

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_link',
    title: 'Vehicle Link',
    type: 'object',
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'uuid', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'class_name', type: 'string', example: 'AEGS_Avenger_Stalker'),
                new OA\Property(property: 'career', type: 'string'),
                new OA\Property(property: 'role', type: 'string'),
                new OA\Property(property: 'size', type: 'integer'),
                new OA\Property(property: 'is_vehicle', type: 'boolean'),
                new OA\Property(property: 'is_gravlev', type: 'boolean'),
                new OA\Property(property: 'is_spaceship', type: 'boolean'),
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
                new OA\Property(property: 'link', type: 'string'),
            ],
            type: 'object',
        ),
        new OA\Schema(ref: '#/components/schemas/metadata'),
    ]
)]
class VehicleLinkResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data->first();

        return [
            'uuid' => $this->uuid,
            'name' => $data->name,
            'class_name' => $data->class_name,
            'career' => $data->career,
            'role' => $data->role,
            'size' => $data->size,
            'is_vehicle' => $data->is_vehicle,
            'is_gravlev' => $data->is_gravlev,
            'is_spaceship' => $data->is_spaceship,
            'manufacturer' => new ManufacturerLinkResource($data->manufacturer),
            'link' => $this->makeApiUrl(self::VEHICLES_SHOW, ($this->uuid ?? urlencode($this->name))),
            'updated_at' => $this->updated_at,
            'version' => $data->gameVersion->code,
        ];
    }
}
