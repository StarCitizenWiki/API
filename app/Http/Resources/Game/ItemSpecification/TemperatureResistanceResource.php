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
            property: 'min',
            description: 'Minimum temperature supported.',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'max',
            description: 'Maximum temperature supported.',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'minimum',
            description: 'Deprecated: Use min.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'maximum',
            description: 'Deprecated: Use max.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
/** @param array $resource Raw temperature resistance data from stdItem.TemperatureResistance sub-array */
class TemperatureResistanceResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'min' => Arr::get($this, 'Minimum'),
            'max' => Arr::get($this, 'Maximum'),
            'minimum' => Arr::get($this, 'Minimum'),  // deprecated: use min
            'maximum' => Arr::get($this, 'Maximum'),  // deprecated: use max
        ];
    }
}
