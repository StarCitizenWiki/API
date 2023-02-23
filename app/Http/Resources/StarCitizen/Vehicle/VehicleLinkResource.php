<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Ship\Ship;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_link',
    title: 'Vehicle Link',
    description: 'Link to the full api page',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'api_url', type: 'string'),
    ],
    type: 'object'
)]
class VehicleLinkResource extends VehicleResource
{
    /**
     * @param Ship $vehicle
     *
     * @return array
     */
    public function transform(Vehicle $vehicle): array
    {
        return [
            'name' => $vehicle->name,
            'slug' => $vehicle->slug,
            'api_url' => $this->makeApiUrl(self::VEHICLES_SHOW, $vehicle->getRouteKey()),
        ];
    }
}
