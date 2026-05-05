<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Manufacturer;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manufacturer',
    title: 'In game manufacturer',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'link', type: 'string'),
    ],
    type: 'object'
)]
class ManufacturerResource extends ManufacturerLinkResource
{
    public static function validIncludes(): array
    {
        return [];
    }
}
