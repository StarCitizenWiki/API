<?php

declare(strict_types=1);

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
                new OA\Property(property: 'uuid', description: 'Unique vehicle identifier.', type: 'string'),
                new OA\Property(property: 'name', description: 'Vehicle display name.', type: 'string'),
                new OA\Property(property: 'class_name', description: 'SC class name of the vehicle.', type: 'string', example: 'AEGS_Avenger_Stalker'),
                new OA\Property(property: 'career', description: 'Vehicle career (e.g. Combat, Exploration, Transport).', type: 'string'),
                new OA\Property(property: 'role', description: 'Vehicle role (e.g. Stealth Fighter, Heavy Freight).', type: 'string'),
                new OA\Property(property: 'size', description: 'Vehicle size class (1–6).', type: 'integer'),
                new OA\Property(property: 'is_vehicle', description: 'Whether this is a ground vehicle.', type: 'boolean'),
                new OA\Property(property: 'is_gravlev', description: 'Whether this is a gravlev vehicle.', type: 'boolean'),
                new OA\Property(property: 'is_spaceship', description: 'Whether this is a spaceship.', type: 'boolean'),
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link', description: 'Vehicle manufacturer.'),
                new OA\Property(property: 'link', description: 'API URL for the full vehicle detail.', type: 'string'),
                new OA\Property(property: 'updated_at', description: 'Timestamp of the last vehicle data update.', type: 'string', format: 'date-time'),
                new OA\Property(property: 'version', description: 'Game version code this vehicle belongs to.', type: 'string', example: '4.4.0-LIVE.12340123'),
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
            'link' => route('vehicles.show', ['vehicle' => $this->uuid ?? $data->name]),
            'updated_at' => $this->updated_at,
            'version' => $data->gameVersion->code,
        ];
    }
}
