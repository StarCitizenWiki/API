<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'turret_axis',
    title: 'Turret Axis',
    description: 'Axis configuration for turret yaw or pitch as emitted by this resource.',
    properties: [
        new OA\Property(
            property: 'slaved_only',
            description: 'Whether the axis is slaved-only (computed from `*.SlavedOnly === 1`).',
            type: 'boolean',
            example: false,
            nullable: true
        ),
        new OA\Property(
            property: 'speed',
            description: 'Axis rotation speed.',
            type: 'double',
            example: 60,
            nullable: true
        ),
        new OA\Property(
            property: 'time_to_full_speed',
            description: 'Seconds to reach full speed (AccelerationTimeToFullSpeed).',
            type: 'double',
            example: 0.5,
            nullable: true
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
            nullable: true
        ),
        new OA\Property(
            property: 'angle_limit_max',
            description: 'Maximum target angle when RestrictTargetAngles is enabled.',
            type: 'double',
            example: 180,
            nullable: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'turret',
    title: 'Turret & Gimbal',
    description: 'Turret/gimbal configuration sourced from stdItem.Turret plus derived mount/size information from extracted ports.',
    properties: [
        new OA\Property(property: 'rotation_style', description: 'Rotation style from stdItem.Turret.RotationStyle.', type: 'string', nullable: true),

        new OA\Property(
            property: 'mounts',
            description: 'Number of weapon mounts derived from the extracted ports count.',
            type: 'integer',
            example: 2,
            nullable: true
        ),
        new OA\Property(
            property: 'min_size',
            description: 'Minimum supported weapon size derived from port MinSize/min_size.',
            type: 'integer',
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: 'max_size',
            description: 'Maximum supported weapon size derived from port MaxSize/max_size.',
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

        $turret = Arr::get($data, 'stdItem.Turret', []);

        $yaw = collect(Arr::get($turret, 'MovementList', []))->firstWhere('JointName', 'yaw_part') ?? [];
        $pitch = collect(Arr::get($turret, 'MovementList', []))->firstWhere('JointName', 'pitch_part') ?? [];

        $ports = collect($this->extractPorts($this->resource));

        return [
            'rotation_style' => Arr::get($turret, 'RotationStyle'),

            'mounts' => $ports->count(),
            'min_size' => $ports->min('MinSize') ?? $ports->min('min_size'),
            'max_size' => $ports->max('MaxSize') ?? $ports->max('max_size'),

            'yaw_axis' => [
                'slaved_only' => Arr::get($yaw, 'YawAxis.SlavedOnly') === 1,
                'speed' => Arr::get($yaw, 'YawAxis.Speed'),
                'time_to_full_speed' => Arr::get($yaw, 'YawAxis.AccelerationTimeToFullSpeed'),
                'acceleration_decay' => Arr::get($yaw, 'YawAxis.AccelerationDecay'),
                $this->mergeWhen(Arr::get($yaw, 'YawAxis.RestrictTargetAngles') === 1, [
                    'angle_limit_min' => Arr::get($yaw, 'YawAxis.AngleLimits.0.LowestAngle'),
                    'angle_limit_max' => Arr::get($yaw, 'YawAxis.AngleLimits.0.HighestAngle'),
                ]),
            ],
            'pitch_axis' => [
                'slaved_only' => Arr::get($pitch, 'PitchAxis.SlavedOnly') === 1,
                'speed' => Arr::get($pitch, 'PitchAxis.Speed'),
                'time_to_full_speed' => Arr::get($pitch, 'PitchAxis.AccelerationTimeToFullSpeed'),
                'acceleration_decay' => Arr::get($pitch, 'PitchAxis.AccelerationDecay'),
                $this->mergeWhen(Arr::get($pitch, 'PitchAxis.RestrictTargetAngles') === 1, [
                    'angle_limit_min' => Arr::get($pitch, 'PitchAxis.AngleLimits.0.LowestAngle'),
                    'angle_limit_max' => Arr::get($pitch, 'PitchAxis.AngleLimits.0.HighestAngle'),
                ]),
            ],
        ];
    }
}
