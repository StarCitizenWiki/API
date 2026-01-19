<?php

use App\Http\Resources\Game\ItemSpecification\SeatResource;
use Illuminate\Http\Request;

it('maps seat data with yaw, pitch, and ejection', function () {
    $payload = [
        'SeatType' => 'HOTAS_C_L',
        'Yaw' => [
            'Minimum' => -70,
            'Maximum' => 70,
        ],
        'Pitch' => [
            'Minimum' => -65,
            'Maximum' => 65,
        ],
        'SetYawPitchLimits' => false,
        'HasEjection' => true,
        'Ejection' => [
            'MaxLinearVelocity' => 2000,
            'MaxLinearAcceleration' => 100,
            'MaxAngularVelocity' => 2000,
            'MaxAngularAcceleration' => 100,
            'EjectionLoopTime' => 1,
        ],
    ];

    $resource = new SeatResource($payload);

    $data = $resource->toArray(new Request);

    expect($data['seat_type'])->toBe('HOTAS_C_L')
        ->and($data['yaw'])->toEqual([
            'minimum' => -70,
            'maximum' => 70,
        ])
        ->and($data['pitch'])->toEqual([
            'minimum' => -65,
            'maximum' => 65,
        ])
        ->and($data['set_yaw_pitch_limits'])->toBeFalse()
        ->and($data['has_ejection'])->toBeTrue()
        ->and($data['ejection'])->toEqual([
            'max_linear_velocity' => 2000,
            'max_linear_acceleration' => 100,
            'max_angular_velocity' => 2000,
            'max_angular_acceleration' => 100,
            'ejection_loop_time' => 1,
        ]);
});

it('returns null ejection when no ejection data is available', function () {
    $payload = [
        'SeatType' => 'HOTAS_C_L',
        'HasEjection' => false,
    ];

    $resource = new SeatResource($payload);

    $data = $resource->toArray(new Request);

    expect($data['ejection'])->toBeNull()
        ->and($data['has_ejection'])->toBeFalse();
});
