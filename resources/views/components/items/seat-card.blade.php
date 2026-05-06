@use('App\Support\Format')
@props([
    'seat',
])

@php
    $seatType = data_get($seat, 'seat_type');
    $setYawPitchLimits = data_get($seat, 'set_yaw_pitch_limits');

    $yaw = data_get($seat, 'yaw', []);
    $yawMin = data_get($yaw, 'minimum');
    $yawMax = data_get($yaw, 'maximum');

    $pitch = data_get($seat, 'pitch', []);
    $pitchMin = data_get($pitch, 'minimum');
    $pitchMax = data_get($pitch, 'maximum');

    $ejection = data_get($seat, 'ejection', []);

@endphp

<x-item-card title="Seat">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Seat Type" :value="$seatType">{{ $seatType }}</x-dt-dd>
            <x-dt-dd label="Has Ejection">{{ is_array($ejection) && $ejection !== [] ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="Ejection Time" :value="data_get($ejection, 'ejection_loop_time')">{{ Format::valueWithUnit(data_get($ejection, 'ejection_loop_time'), 's', 2) }}</x-dt-dd>
        </x-slot:head>

        <x-dl-details title="Axis Limits" :open="true" testId="seat-card-axis-limits">
            <x-dt-dd label="Set Yaw/Pitch Limits" :value="$yawMin ?? $yawMax ?? $pitchMin ?? $pitchMax">{{ $setYawPitchLimits ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="Yaw" :value="$yawMin ?? $yawMax">{{ Format::range($yawMin, $yawMax, 'deg', 2) }}</x-dt-dd>
            <x-dt-dd label="Pitch" :value="$pitchMin ?? $pitchMax">{{ Format::range($pitchMin, $pitchMax, 'deg', 2) }}</x-dt-dd>
        </x-dl-details>

        <x-dl-details title="Ejection" testId="seat-card-ejection">
            <x-dt-dd label="Max Linear Velocity" :value="data_get($ejection, 'max_linear_velocity')">{{ Format::valueWithUnit(data_get($ejection, 'max_linear_velocity'), 'm/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Max Linear Acceleration" :value="data_get($ejection, 'max_linear_acceleration')">{{ Format::valueWithUnit(data_get($ejection, 'max_linear_acceleration'), 'm/s²', 2) }}</x-dt-dd>
            <x-dt-dd label="Max Angular Velocity" :value="data_get($ejection, 'max_angular_velocity')">{{ Format::valueWithUnit(data_get($ejection, 'max_angular_velocity'), 'rad/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Max Angular Acceleration" :value="data_get($ejection, 'max_angular_acceleration')">{{ Format::valueWithUnit(data_get($ejection, 'max_angular_acceleration'), 'rad/s²', 2) }}</x-dt-dd>
        </x-dl-details>
    </x-dl-container>
</x-item-card>
