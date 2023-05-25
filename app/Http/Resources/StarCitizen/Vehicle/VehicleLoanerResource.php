<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_loaner_v2',
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
            'link' => $this->makeApiUrl(
                self::VEHICLES_SHOW,
                $this->sc?->exists ? $this->sc->item_uuid : urlencode($this->name)
            ),

            'version' => $this->pivot->version,
        ];
    }
}
