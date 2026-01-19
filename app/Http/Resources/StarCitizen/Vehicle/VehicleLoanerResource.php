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
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'link', type: 'string'),
        new OA\Property(property: 'version', type: 'string'),
    ],
    type: 'object'
)]
class VehicleLoanerResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'link' => route(
                'vehicles.show',
                ['vehicle' => $this->sc?->exists ? $this->sc->vehicle->uuid : ($this->name ?? '')]
            ),
            'version' => $this->pivot->version,
        ];
    }
}
