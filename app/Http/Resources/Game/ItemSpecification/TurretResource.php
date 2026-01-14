<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'turret',
    title: 'Turret & Gimbal',
    properties: [
        new OA\Property(property: 'rotation_style', type: 'string'),
        new OA\Property(property: 'max_mounts', type: 'integer'),
        new OA\Property(property: 'min_size', type: 'integer'),
        new OA\Property(property: 'max_size', type: 'integer'),

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
                $this->mergeWhen(Arr::get($yaw, 'PitchAxis.RestrictTargetAngles') === 1, [
                    'angle_limit_min' => Arr::get($pitch, 'PitchAxis.AngleLimits.0.LowestAngle'),
                    'angle_limit_max' => Arr::get($pitch, 'PitchAxis.AngleLimits.0.HighestAngle'),
                ]),
            ],
        ];
    }
}
