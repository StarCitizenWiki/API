<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'fuel_intake',
    title: 'Fuel Intake',
    description: 'Fuel intake flow rates sourced from stdItem.FuelIntake.',
    properties: [
        new OA\Property(property: 'fuel_push_rate', description: 'Fuel push rate into the fuel tanks. A value of 0 means no active push capability.', type: 'double', nullable: true),
        new OA\Property(property: 'minimum_rate', description: 'Minimum fuel collection rate.', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class FuelIntakeResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $flowRates = Arr::get($stdItem, 'FuelIntake.FlowRates', Arr::get($stdItem, 'FuelIntake', []));

        return [
            'fuel_push_rate' => Arr::get($flowRates, 'FuelPushRate', Arr::get($flowRates, 'fuelPushRate')),
            'minimum_rate' => Arr::get($flowRates, 'MinimumRate', Arr::get($flowRates, 'minimumRate')),
        ];
    }
}
