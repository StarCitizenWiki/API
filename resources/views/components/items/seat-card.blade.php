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
    $hasYaw = is_array($yaw) && collect($yaw)->filter(fn($v) => $v !== null)->isNotEmpty();

    $pitch = data_get($seat, 'pitch', []);
    $pitchMin = data_get($pitch, 'minimum');
    $pitchMax = data_get($pitch, 'maximum');
    $hasPitch = is_array($pitch) && collect($pitch)->filter(fn($v) => $v !== null)->isNotEmpty();

    $hasEjection = data_get($seat, 'has_ejection');
    $ejection = data_get($seat, 'ejection', []);
    $hasEjectionData = is_array($ejection) && $ejection !== [];

    // Count visible ejection fields for tertiary section
    $ejectionFieldCount = 0;
    if (data_get($ejection, 'max_linear_velocity') !== null) {
        $ejectionFieldCount++;
    }
    if (data_get($ejection, 'max_linear_acceleration') !== null) {
        $ejectionFieldCount++;
    }
    if (data_get($ejection, 'max_angular_velocity') !== null) {
        $ejectionFieldCount++;
    }
    if (data_get($ejection, 'max_angular_acceleration') !== null) {
        $ejectionFieldCount++;
    }
    if (data_get($ejection, 'ejection_loop_time') !== null) {
        $ejectionFieldCount++;
    }
    $showTertiary = $ejectionFieldCount >= 2;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Seat</h2>

        {{-- Primary Data (Always Visible) --}}
        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <x-dt-dd label="Seat Type">{{ $seatType }}</x-dt-dd>
            <x-dt-dd label="Has Ejection">{{ $hasEjectionData ? 'Yes' : 'No' }}</x-dt-dd>
            @if (data_get($ejection, 'ejection_loop_time') !== null)
                <x-dt-dd label="Ejection Time">{{ Format::valueWithUnit(data_get($ejection, 'ejection_loop_time'), 's', 2) }}</x-dt-dd>
            @endif
        </x-dl-section>

        {{-- Secondary Data (Collapsible, default open) --}}
        @if ($hasYaw || $hasPitch)
            <x-dl-details title="Axis Limits" :open="true" testId="seat-card-axis-limits">
                <x-dt-dd label="Set Yaw/Pitch Limits">{{ $setYawPitchLimits ? 'Yes' : 'No' }}</x-dt-dd>
                @if ($hasYaw)
                    <x-dt-dd label="Yaw">{{ Format::range($yawMin, $yawMax, 'deg', 2) }}</x-dt-dd>
                @endif
                @if ($hasPitch)
                    <x-dt-dd label="Pitch">{{ Format::range($pitchMin, $pitchMax, 'deg', 2) }}</x-dt-dd>
                @endif
            </x-dl-details>
        @endif

        {{-- Tertiary Data (Collapsible, default closed) --}}
        @if ($hasEjection && $hasEjectionData && $showTertiary)
            <x-dl-details title="Ejection" testId="seat-card-ejection">
                @if (data_get($ejection, 'max_linear_velocity') !== null)
                    <x-dt-dd label="Max Linear Velocity">{{ Format::valueWithUnit(data_get($ejection, 'max_linear_velocity'), 'm/s', 2) }}</x-dt-dd>
                @endif
                @if (data_get($ejection, 'max_linear_acceleration') !== null)
                    <x-dt-dd label="Max Linear Acceleration">{{ Format::valueWithUnit(data_get($ejection, 'max_linear_acceleration'), 'm/s²', 2) }}</x-dt-dd>
                @endif
                @if (data_get($ejection, 'max_angular_velocity') !== null)
                    <x-dt-dd label="Max Angular Velocity">{{ Format::valueWithUnit(data_get($ejection, 'max_angular_velocity'), 'rad/s', 2) }}</x-dt-dd>
                @endif
                @if (data_get($ejection, 'max_angular_acceleration') !== null)
                    <x-dt-dd label="Max Angular Acceleration">{{ Format::valueWithUnit(data_get($ejection, 'max_angular_acceleration'), 'rad/s²', 2) }}</x-dt-dd>
                @endif
            </x-dl-details>
        @endif
    </div>
</div>
