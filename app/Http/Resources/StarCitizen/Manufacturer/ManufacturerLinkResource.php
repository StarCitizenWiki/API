<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Manufacturer;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shipmatrix_manufacturer_link',
    title: 'Link to the detail page of a Ship-Matrix manufacturer',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'code', type: 'string'),
    ],
    type: 'object'
)]
class ManufacturerLinkResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cig_id,
            'name' => $this->name,
            'code' => $this->name_short,
        ];
    }
}
