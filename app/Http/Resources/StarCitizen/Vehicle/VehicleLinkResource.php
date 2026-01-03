<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\StarCitizen\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\TranslationResolver;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shipmatrix_vehicle_link',
    title: 'Ship-Matrix Vehicle Link',
    type: 'object',
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'slug', type: 'string'),
                new OA\Property(property: 'size', ref: '#/components/schemas/translation'),
                new OA\Property(property: 'type', ref: '#/components/schemas/translation'),
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/shipmatrix_manufacturer_link'),
                new OA\Property(property: 'production_status', ref: '#/components/schemas/translation'),
                new OA\Property(property: 'link', type: 'string'),
                new OA\Property(property: 'updated_at', type: 'string'),
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
        return [
            'id' => $this->cig_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'size' => TranslationResolver::resolve($this->size, $request),
            'type' => TranslationResolver::resolve($this->type, $request),
            'manufacturer' => new ManufacturerLinkResource($this->manufacturer),
            'production_status' => TranslationResolver::resolve($this->productionStatus, $request),
            'link' => route('shipmatrix.vehicles.show', ['vehicle' => $this->slug]),
            'updated_at' => $this->updated_at,
        ];
    }
}
