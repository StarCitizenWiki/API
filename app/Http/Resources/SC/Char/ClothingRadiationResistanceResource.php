<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'clothing_radiation_resistance_v2',
    title: 'Clothing Radiation Resistance',
    description: 'Radiation Resistance of Clothes or Armors',
    properties: [
        new OA\Property(
            property: 'maximum_radiation_capacity',
            description: 'Radiation Protection',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'radiation_dissipation_rate',
            description: 'Scrub Rate',
            type: 'double',
            nullable: true
        ),
    ],
    type: 'object'
)]
class ClothingRadiationResistanceResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'maximum_radiation_capacity' => $this->maximum_radiation_capacity,
            'radiation_dissipation_rate' => $this->radiation_dissipation_rate,
        ];
    }
}
