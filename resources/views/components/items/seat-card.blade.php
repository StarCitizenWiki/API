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
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Seat Type</dt>
                <dd class="text-sm font-semibold text-base-content">{{ $seatType }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Has Ejection</dt>
                <dd class="text-sm font-semibold text-base-content">{{ $hasEjectionData ? 'Yes' : 'No' }}</dd>
            </div>
            @if (data_get($ejection, 'ejection_loop_time') !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Ejection Time</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($ejection, 'ejection_loop_time'), 's', 2) }}</dd>
                </div>
            @endif
        </dl>

        {{-- Secondary Data (Collapsible, default open) --}}
        @if ($hasYaw || $hasPitch)
            <details class="group" data-testid="seat-card-axis-limits" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Axis Limits
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted">Set Yaw/Pitch Limits</dt>
                            <dd class="text-sm font-semibold text-base-content">{{ $setYawPitchLimits ? 'Yes' : 'No' }}</dd>
                        </div>
                        @if ($hasYaw)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Yaw</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_range($yawMin, $yawMax, 'deg', 2) }}</dd>
                            </div>
                        @endif
                        @if ($hasPitch)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pitch</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_range($pitchMin, $pitchMax, 'deg', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        {{-- Tertiary Data (Collapsible, default closed) --}}
        @if ($hasEjection && $hasEjectionData && $showTertiary)
            <details class="group" data-testid="seat-card-ejection">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Ejection
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($ejection, 'max_linear_velocity') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Linear Velocity</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($ejection, 'max_linear_velocity'), 'm/s', 2) }}</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_linear_acceleration') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Linear Acceleration</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($ejection, 'max_linear_acceleration'), 'm/s²', 2) }}</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_angular_velocity') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Angular Velocity</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($ejection, 'max_angular_velocity'), 'rad/s', 2) }}</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_angular_acceleration') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Angular Acceleration</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($ejection, 'max_angular_acceleration'), 'rad/s²', 2) }}</dd>
                            </div>
                        @endif

                    </dl>
            </details>
        @endif
    </div>
</div>
