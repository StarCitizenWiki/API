<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Manufacturer;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manufacturer_link',
    title: 'Link to the detail page of a manufacturer',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'link', type: 'string'),
    ],
    type: 'object'
)]
class ManufacturerLinkResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'uuid' => $this->uuid,
            'link' => route('manufacturers.show', ['manufacturer' => empty($this->code) ? 'UNKN' : $this->code]),
        ];
    }
}
