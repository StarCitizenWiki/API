<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_link_v2',
    title: 'Vehicle',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'link', type: 'string'),
        new OA\Property(property: 'updated_at', type: 'string'),
        new OA\Property(property: 'version', type: 'string'),

    ],
    type: 'object'
)]
class VehicleLinkResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'sc.item.shops',
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->item_uuid ?? $this->sc->item_uuid ?? urlencode($this->name),
            'name' => $this->name,
            'link' => $this->makeApiUrl(self::VEHICLES_SHOW, ($this->item_uuid ?? $this->sc->item_uuid ?? urlencode($this->name))),
            'updated_at' => $this->updated_at,
            'version' => $this->item->version,
        ];
    }
}
