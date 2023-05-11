<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Manufacturer;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manufacturer_link_v2',
    title: 'Manufacturer Link',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'link', type: 'string'),
        new OA\Property(property: 'ships_count', type: 'integer'),
        new OA\Property(property: 'vehicles_count', type: 'integer'),
        new OA\Property(property: 'items_count', type: 'integer'),
    ],
    type: 'object'
)]
class ManufacturerLinkResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $include = $request->get('include', '');
        if (empty($include)) {
            $include = '';
        }

        return [
            'name' => $this->name,
            'code' => $this->code,
            'link' => $this->makeApiUrl(self::MANUFACTURERS_SHOW, urlencode($this->name)),
            $this->mergeWhen(str_contains($include, 'counts'), [
                'ships_count' => $this->shipsCount(),
                'vehicles_count' => $this->groundVehiclesCount(),
                'items_count' => $this->itemsCount(),
            ]),
        ];
    }
}
