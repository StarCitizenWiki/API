<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'seat_axis',
    title: 'Item Seat Axis Limits',
    description: 'Minimum and maximum values for a yaw or pitch axis.',
    properties: [
        new OA\Property(property: 'minimum', type: 'double', nullable: true),
        new OA\Property(property: 'maximum', type: 'double', nullable: true),
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
        new OA\Property(property: 'yaw', ref: '#/components/schemas/item_seat_axis', nullable: true),
        new OA\Property(property: 'pitch', ref: '#/components/schemas/item_seat_axis', nullable: true),
        new OA\Property(property: 'set_yaw_pitch_limits', type: 'boolean', nullable: true),
        new OA\Property(property: 'has_ejection', type: 'boolean', nullable: true),
        new OA\Property(property: 'ejection', ref: '#/components/schemas/item_seat_ejection', nullable: true),
    ],
    type: 'object',
)]
class SeatResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $ejection = Arr::get($this, 'Ejection');

        return [
            'seat_type' => Arr::get($this, 'SeatType'),
            'yaw' => $this->axisLimits(Arr::get($this, 'Yaw')),
            'pitch' => $this->axisLimits(Arr::get($this, 'Pitch')),
            'set_yaw_pitch_limits' => Arr::get($this, 'SetYawPitchLimits'),
            'has_ejection' => Arr::get($this, 'HasEjection'),
            'ejection' => $this->ejectionData($ejection),
        ];
    }

    private function axisLimits(?array $axis): ?array
    {
        if (empty($axis)) {
            return null;
        }

        return [
            'minimum' => Arr::get($axis, 'Minimum'),
            'maximum' => Arr::get($axis, 'Maximum'),
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
