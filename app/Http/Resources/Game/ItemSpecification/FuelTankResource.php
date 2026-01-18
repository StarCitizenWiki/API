<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'fuel_tank',
    title: 'Fuel Tank',
    description: 'Fuel and quantum fuel tank characteristics.',
    properties: [
        new OA\Property(
            property: 'fill_rate',
            description: 'Maximum generation/refill rate in standard resource units per second. Typical civilian tanks are 0.25–10.',
            type: 'double',
            example: 10,
            nullable: true
        ),
        new OA\Property(
            property: 'drain_rate',
            description: 'Maximum consumption/usage rate in standard resource units per second.',
            type: 'double',
            example: 10,
            nullable: true
        ),
        new OA\Property(
            property: 'discharge_rate',
            description: 'Configured discharge rate; most tanks use 0.',
            type: 'double',
            example: 0,
            nullable: true
        ),

    ],
    type: 'object'
)]
class FuelTankResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);

        return [
            'fill_rate' => Arr::get($stdItem, 'ResourceNetwork.States.0.Deltas.0.GenerateRate'),
            'drain_rate' => Arr::get($stdItem, 'ResourceNetwork.States.0.Deltas.0.Discharge'),
            'capacity' => Arr::get($stdItem, 'FuelTank.Capacity'),
        ];
    }
}
