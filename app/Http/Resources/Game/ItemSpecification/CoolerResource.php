<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'cooler',
    title: 'Cooler',
    description: 'Use Resource Network data for actual cooling segment generation. Ship cooler characteristics describing heat dissipation capacity and signature suppression factors.',
    properties: [
        new OA\Property(
            property: 'cooling_rate',
            description: 'Maximum heat removal capacity (heat units per second). Higher numbers indicate stronger cooling performance. Size 1 military coolers like the Aegis Glacier are around 290,000 while heavy industrial units reach into the tens of millions.',
            type: 'double',
            example: 4080000,
            nullable: true
        ),
        new OA\Property(
            property: 'suppression_ir_factor',
            description: 'Infrared signature multiplier applied while the cooler is operating. Values below 1.0 lower IR output; most production coolers use 0.1 (90% reduction).',
            type: 'double',
            example: 0.1,
            nullable: true
        ),
        new OA\Property(
            property: 'suppression_heat_factor',
            description: 'Overall heat signature multiplier contributed by the cooler. Commonly 0.1, indicating a significant reduction in emitted heat.',
            type: 'double',
            example: 0.1,
            nullable: true
        ),
    ],
    type: 'object'
)]
class CoolerResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $cooler = Arr::get($data, 'stdItem.Cooler', []);

        return [
            'cooling_rate' => Arr::get($cooler, 'CoolingRate'),
            'suppression_ir_factor' => Arr::get($cooler, 'SuppressionIRFactor'),
            'suppression_heat_factor' => Arr::get($cooler, 'SuppressionHeatFactor'),
        ];
    }
}
