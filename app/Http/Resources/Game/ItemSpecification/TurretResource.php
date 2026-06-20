<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'turret_axis',
    title: 'Turret Axis',
    description: 'Axis configuration for turret yaw or pitch.',
    properties: [
        new OA\Property(
            property: 'slaved_only',
            description: 'Whether the axis is slaved-only.',
            type: 'boolean',
            example: false,
            nullable: true
        ),
        new OA\Property(
            property: 'speed',
            description: 'Axis rotation speed.',
            type: 'double',
            example: 60,
            nullable: true,
            x: ['suffix' => ' °/s']
        ),
        new OA\Property(
            property: 'time_to_full_speed',
            description: 'Seconds to reach full rotation speed.',
            type: 'double',
            example: 0.5,
            nullable: true,
            x: ['suffix' => ' s']
        ),
        new OA\Property(
            property: 'acceleration_decay',
            description: 'Acceleration decay value.',
            type: 'double',
            example: 0.0,
            nullable: true
        ),
        new OA\Property(
            property: 'angle_limit_min',
            description: 'Minimum target angle when RestrictTargetAngles is enabled.',
            type: 'double',
            example: -180,
            nullable: true,
            x: ['suffix' => ' °']
        ),
        new OA\Property(
            property: 'angle_limit_max',
            description: 'Maximum target angle when RestrictTargetAngles is enabled.',
            type: 'double',
            example: 180,
            nullable: true,
            x: ['suffix' => ' °']
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'turret',
    title: 'Turret & Gimbal',
    description: 'Turret or gimbal configuration including rotation style, weapon size constraints, and axis parameters.',
    properties: [
        new OA\Property(property: 'rotation_style', description: 'Turret rotation style.', type: 'string', nullable: true),

        new OA\Property(
            property: 'mounts',
            description: 'Number of weapon mounts.',
            type: 'integer',
            example: 2,
            nullable: true
        ),
        new OA\Property(
            property: 'min_size',
            description: 'Minimum supported weapon size.',
            type: 'integer',
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: 'max_size',
            description: 'Maximum supported weapon size.',
            type: 'integer',
            example: 3,
            nullable: true
        ),

        new OA\Property(property: 'yaw_axis', ref: '#/components/schemas/turret_axis', nullable: true),
        new OA\Property(property: 'pitch_axis', ref: '#/components/schemas/turret_axis', nullable: true),
    ],
    type: 'object'
)]
class TurretResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $turret = $data['stdItem']['Turret'] ?? [];
        $movementList = $turret['MovementList'] ?? [];

        $yaw = collect($movementList)->firstWhere('JointName', 'yaw_part') ?? [];
        $pitch = collect($movementList)->firstWhere('JointName', 'pitch_part') ?? [];

        $yawAxis = $yaw['YawAxis'] ?? null;
        $pitchAxis = $pitch['PitchAxis'] ?? null;

        $ports = collect($this->extractPorts($this->resource));

        return [
            'rotation_style' => $turret['RotationStyle'] ?? null,

            'mounts' => $ports->count(),
            'min_size' => $ports->min('MinSize') ?? $ports->min('min_size'),
            'max_size' => $ports->max('MaxSize') ?? $ports->max('max_size'),

            'yaw_axis' => [
                'slaved_only' => ($yawAxis['SlavedOnly'] ?? null) === 1,
                'speed' => $yawAxis['Speed'] ?? null,
                'time_to_full_speed' => $yawAxis['AccelerationTimeToFullSpeed'] ?? null,
                'acceleration_decay' => $yawAxis['AccelerationDecay'] ?? null,
                $this->mergeWhen(($yawAxis['RestrictTargetAngles'] ?? null) === 1, [
                    'angle_limit_min' => $yawAxis['AngleLimits'][0]['LowestAngle'] ?? null,
                    'angle_limit_max' => $yawAxis['AngleLimits'][0]['HighestAngle'] ?? null,
                ]),
            ],
            'pitch_axis' => [
                'slaved_only' => ($pitchAxis['SlavedOnly'] ?? null) === 1,
                'speed' => $pitchAxis['Speed'] ?? null,
                'time_to_full_speed' => $pitchAxis['AccelerationTimeToFullSpeed'] ?? null,
                'acceleration_decay' => $pitchAxis['AccelerationDecay'] ?? null,
                $this->mergeWhen(($pitchAxis['RestrictTargetAngles'] ?? null) === 1, [
                    'angle_limit_min' => $pitchAxis['AngleLimits'][0]['LowestAngle'] ?? null,
                    'angle_limit_max' => $pitchAxis['AngleLimits'][0]['HighestAngle'] ?? null,
                ]),
            ],
        ];
    }
}
