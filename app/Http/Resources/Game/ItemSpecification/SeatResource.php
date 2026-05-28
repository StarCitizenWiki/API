<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'seat_axis',
    title: 'Item Seat Axis Limits',
    description: 'Minimum and maximum values for a yaw or pitch axis.',
    properties: [
        new OA\Property(property: 'min', description: 'Minimum axis limit.', type: 'double', nullable: true),
        new OA\Property(property: 'max', description: 'Maximum axis limit.', type: 'double', nullable: true),
        new OA\Property(property: 'minimum', description: 'Deprecated: Use min.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
    ],
    type: 'object',
)]

#[OA\Schema(
    schema: 'seat_ejection',
    title: 'Item Seat Ejection',
    description: 'Ejection system capabilities when equipped.',
    properties: [
        new OA\Property(property: 'max_linear_velocity', description: 'Maximum linear ejection velocity in meters per second.', type: 'double', nullable: true, x: ['suffix' => ' m/s']),
        new OA\Property(property: 'max_linear_acceleration', description: 'Maximum linear ejection acceleration in meters per second squared.', type: 'double', nullable: true, x: ['suffix' => ' m/s²']),
        new OA\Property(property: 'max_angular_velocity', description: 'Maximum angular ejection velocity.', type: 'double', nullable: true),
        new OA\Property(property: 'max_angular_acceleration', description: 'Maximum angular ejection acceleration.', type: 'double', nullable: true),
        new OA\Property(property: 'ejection_loop_time', description: 'Ejection sequence duration in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
    ],
    type: 'object',
)]

#[OA\Schema(
    schema: 'seat',
    title: 'Seat',
    description: 'Seat parameters including type, axis limits, and ejection system.',
    properties: [
        new OA\Property(property: 'seat_type', description: 'Seat control type.', type: 'string', nullable: true),
        new OA\Property(property: 'yaw', description: 'Yaw axis rotation limits.', ref: '#/components/schemas/seat_axis', nullable: true),
        new OA\Property(property: 'pitch', description: 'Pitch axis rotation limits.', ref: '#/components/schemas/seat_axis', nullable: true),
        new OA\Property(property: 'set_yaw_pitch_limits', description: 'Whether yaw and pitch limits are enforced.', type: 'boolean', nullable: true),
        new OA\Property(property: 'has_ejection', description: 'Whether the seat has an ejection system.', type: 'boolean', nullable: true),
        new OA\Property(property: 'ejection', description: 'Ejection system parameters.', ref: '#/components/schemas/seat_ejection', nullable: true),
    ],
    type: 'object',
)]
class SeatResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $seat = $this->extractFromStdItem($this->resource, 'Seat');
        $ejection = Arr::get($seat, 'Ejection');

        return [
            'seat_type' => Arr::get($seat, 'SeatType'),
            'yaw' => $this->axisLimits(Arr::get($seat, 'Yaw')),
            'pitch' => $this->axisLimits(Arr::get($seat, 'Pitch')),
            'set_yaw_pitch_limits' => Arr::get($seat, 'SetYawPitchLimits'),
            'has_ejection' => $this->hasEjection($ejection),
            'ejection' => $this->ejectionData($ejection),
        ];
    }

    private function axisLimits(?array $axis): ?array
    {
        if (empty($axis)) {
            return null;
        }

        return [
            'min' => Arr::get($axis, 'Minimum'),
            'max' => Arr::get($axis, 'Maximum'),
            'minimum' => Arr::get($axis, 'Minimum'),  // deprecated: use min
            'maximum' => Arr::get($axis, 'Maximum'),  // deprecated: use max
        ];
    }

    private function hasEjection(?array $ejection): bool
    {
        return is_array($ejection) && $ejection !== [];
    }

    private function ejectionData(?array $ejection): ?array
    {
        if (! $this->hasEjection($ejection)) {
            return null;
        }

        return [
            'max_linear_velocity' => Arr::get($ejection, 'MaxLinearVelocity'),
            'max_linear_acceleration' => Arr::get($ejection, 'MaxLinearAcceleration'),
            'max_angular_velocity' => Arr::get($ejection, 'MaxAngularVelocity'),
            'max_angular_acceleration' => Arr::get($ejection, 'MaxAngularAcceleration'),
            'ejection_loop_time' => Arr::get($ejection, 'EjectionLoopTime'),
        ];
    }
}
