<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'power_plant',
    title: 'Power Plant',
    description: 'Power plant specifications including power output and segment generation from resource network.',
    properties: [
        new OA\Property(property: 'power_output', type: 'double', nullable: true),
        new OA\Property(
            property: 'power_segment_generation',
            description: 'Power segment generation rate from resource network. Use this for actual power segment generation.',
            type: 'double',
            example: 1000,
            nullable: true
        ),
    ],
    type: 'object'
)]
class PowerPlantResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $power = Arr::get($stdItem, 'PowerConnection', []);

        if ($power === []) {
            $power = Arr::get($data, 'Raw.Entity.Components.EntityComponentPowerConnection', []);
        }

        return [
            'power_output' => Arr::get($power, 'PowerDraw', Arr::get($power, 'powerDraw')),
            'power_segment_generation' => Arr::get($stdItem, 'ResourceNetwork.Generation.Power'),
        ];
    }
}
