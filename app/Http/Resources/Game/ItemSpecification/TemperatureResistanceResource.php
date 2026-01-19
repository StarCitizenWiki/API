<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'temperature_resistance',
    title: 'Temperature Resistance',
    description: 'Temperature resistance range shared by clothing and armor.',
    properties: [
        new OA\Property(
            property: 'minimum',
            description: 'Minimum temperature supported.',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'maximum',
            description: 'Maximum temperature supported.',
            type: 'double',
            nullable: true
        ),
    ],
    type: 'object'
)]
class TemperatureResistanceResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'minimum' => Arr::get($this, 'Minimum'),
            'maximum' => Arr::get($this, 'Maximum'),
        ];
    }
}
