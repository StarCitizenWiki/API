<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'radar',
    title: 'Radar',
    description: 'Radar ',
    properties: [
        new OA\Property(property: 'detection_lifetime', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'altitude_ceiling', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'enable_cross_section_occlusion', type: 'boolean', nullable: true, deprecated: true),
    ],
    type: 'object'
)]
class RadarResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $radar = Arr::get($stdItem, 'Radar', []);

        return [
            'detection_lifetime' => null,
            'altitude_ceiling' => null,
            'enable_cross_section_occlusion' => null,

            'cooldown' => Arr::get($radar, 'Cooldown'),
            'sensitivity' => [
                'infrared' => Arr::get($radar, 'Sensitivity.IR'),
                'cross_section' => Arr::get($radar, 'Sensitivity.CS'),
                'electromagnetic' => Arr::get($radar, 'Sensitivity.EM'),
                'resource' => Arr::get($radar, 'Sensitivity.RS'),
                'db' => Arr::get($radar, 'Sensitivity.dB'),
            ],
            'ground_vehicle_sensitivity' => [
                'infrared' => Arr::get($radar, 'GroundVehicleDetectionSensitivity.IR'),
                'cross_section' => Arr::get($radar, 'GroundVehicleDetectionSensitivity.CS'),
                'electromagnetic' => Arr::get($radar, 'GroundVehicleDetectionSensitivity.EM'),
                'resource' => Arr::get($radar, 'GroundVehicleDetectionSensitivity.RS'),
                'db' => Arr::get($radar, 'GroundVehicleDetectionSensitivity.dB'),
            ],
            'piercing' => [
                'infrared' => Arr::get($radar, 'Piercing.IR'),
                'cross_section' => Arr::get($radar, 'Piercing.CS'),
                'electromagnetic' => Arr::get($radar, 'Piercing.EM'),
                'resource' => Arr::get($radar, 'Piercing.RS'),
                'db' => Arr::get($radar, 'Piercing.dB'),
            ],
        ];
    }
}
