<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_loaner',
    title: 'Vehicle Loaner',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique vehicle identifier.', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', description: 'URL-friendly vehicle identifier.', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string'),
        new OA\Property(property: 'version', type: 'string'),
    ],
    type: 'object'
)]
class VehicleLoanerResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $hasGameVehicle = $this->sc?->exists ?? false;

        return [
            'uuid' => $hasGameVehicle ? $this->sc->vehicle->uuid : null,
            'name' => $this->name,
            'slug' => $hasGameVehicle ? $this->sc->vehicle->slug : $this->slug,
            'link' => route(
                'vehicles.show',
                ['vehicle' => $hasGameVehicle ? $this->sc->vehicle->uuid : ($this->name ?? '')]
            ),
            'version' => $this->pivot->version,
        ];
    }
}
