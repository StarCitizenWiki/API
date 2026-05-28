<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'radar_sensitivity_block',
    title: 'Radar Sensitivity Block',
    description: 'Per-signal sensitivity values as provided by game data.',
    properties: [
        new OA\Property(property: 'infrared', description: 'Infrared signature detection sensitivity.', type: 'double', nullable: true),
        new OA\Property(property: 'cross_section', description: 'Cross-section signature detection sensitivity.', type: 'double', nullable: true),
        new OA\Property(property: 'electromagnetic', description: 'Electromagnetic signature detection sensitivity.', type: 'double', nullable: true),
        new OA\Property(property: 'resource', description: 'Resource signature detection sensitivity.', type: 'double', nullable: true),
        new OA\Property(property: 'db', description: 'Decibel signature detection sensitivity.', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'radar_aim_assist_block',
    title: 'Radar Aim Assist Block',
    description: 'Aim assist range parameters as provided by game data.',
    properties: [
        new OA\Property(property: 'distance_min_assignment', description: 'Minimum distance for aim assist target assignment in meters.', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'distance_max_assignment', description: 'Maximum distance for aim assist target assignment in meters.', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'outside_range_buffer_distance', description: 'Buffer distance beyond max range in meters.', type: 'double', nullable: true, x: ['suffix' => ' m']),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'radar',
    title: 'Radar',
    description: 'Radar detection parameters including sensitivity, piercing, and aim assist.',
    properties: [
        // Legacy placeholders (always null in this resource)
        new OA\Property(
            property: 'detection_lifetime',
            description: 'Deprecated legacy field. Always null in this resource output.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'altitude_ceiling',
            description: 'Deprecated legacy field. Always null in this resource output.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'enable_cross_section_occlusion',
            description: 'Deprecated legacy field. Always null in this resource output.',
            type: 'boolean',
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'cooldown',
            description: 'Radar ping cooldown in seconds.',
            type: 'double',
            example: 1.0,
            nullable: true,
            x: ['suffix' => ' s']
        ),

        new OA\Property(
            property: 'sensitivity',
            ref: '#/components/schemas/radar_sensitivity_block',
            description: 'Detection sensitivity per signature type.',
            nullable: true
        ),
        new OA\Property(
            property: 'ground_vehicle_sensitivity',
            ref: '#/components/schemas/radar_sensitivity_block',
            description: 'Detection sensitivity for ground vehicles, derived from sensitivity modifiers.',
            nullable: true
        ),
        new OA\Property(
            property: 'piercing',
            ref: '#/components/schemas/radar_sensitivity_block',
            description: 'Signal piercing strength per signature type, controlling detection through occlusion.',
            nullable: true
        ),
        new OA\Property(
            property: 'aim_assist',
            ref: '#/components/schemas/radar_aim_assist_block',
            description: 'Aim assist range parameters.',
            nullable: true
        ),
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
            'aim_assist' => [
                'distance_min_assignment' => Arr::get($radar, 'AimAssist.DistanceMinAssignment'),
                'distance_max_assignment' => Arr::get($radar, 'AimAssist.DistanceMaxAssignment'),
                'outside_range_buffer_distance' => Arr::get($radar, 'AimAssist.OutsideRangeBufferDistance'),
            ],
        ];
    }
}
