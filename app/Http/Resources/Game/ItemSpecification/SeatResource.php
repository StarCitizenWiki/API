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
        new OA\Property(property: 'min', type: 'double', nullable: true),
        new OA\Property(property: 'max', type: 'double', nullable: true),
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
        new OA\Property(property: 'max_linear_velocity', type: 'double', nullable: true),
        new OA\Property(property: 'max_linear_acceleration', type: 'double', nullable: true),
        new OA\Property(property: 'max_angular_velocity', type: 'double', nullable: true),
        new OA\Property(property: 'max_angular_acceleration', type: 'double', nullable: true),
        new OA\Property(property: 'ejection_loop_time', type: 'double', nullable: true),
    ],
    type: 'object',
)]

#[OA\Schema(
    schema: 'seat',
    title: 'Seat',
    description: 'Seat data sourced from stdItem.Seat for manned components.',
    properties: [
        new OA\Property(property: 'seat_type', type: 'string', nullable: true),
        new OA\Property(property: 'yaw', ref: '#/components/schemas/seat_axis', nullable: true),
        new OA\Property(property: 'pitch', ref: '#/components/schemas/seat_axis', nullable: true),
        new OA\Property(property: 'set_yaw_pitch_limits', type: 'boolean', nullable: true),
        new OA\Property(property: 'has_ejection', type: 'boolean', nullable: true),
        new OA\Property(property: 'ejection', ref: '#/components/schemas/seat_ejection', nullable: true),
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
