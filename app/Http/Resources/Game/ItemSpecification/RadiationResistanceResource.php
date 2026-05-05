<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'radiation_resistance',
    title: 'Radiation Resistance',
    description: 'Radiation resistance values used by clothing and armor.',
    properties: [
        new OA\Property(
            property: 'maximum_radiation_capacity',
            description: 'MaximumRadiationCapacity attribute (e.g. radiation protection).',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'radiation_dissipation_rate',
            description: 'RadiationDissipationRate attribute (scrub rate).',
            type: 'double',
            nullable: true
        ),
    ],
    type: 'object'
)]
/** @param array $resource Raw radiation resistance data from stdItem.RadiationResistance sub-array */
class RadiationResistanceResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'maximum_radiation_capacity' => Arr::get($this, 'MaximumRadiationCapacity'),
            'radiation_dissipation_rate' => Arr::get($this, 'RadiationDissipationRate'),
        ];
    }
}
