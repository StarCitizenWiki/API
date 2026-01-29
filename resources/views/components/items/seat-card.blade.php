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
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="chair" class="size-4 text-primary" />
            <span>Seat</span>
        </h2>

        {{-- Primary Data (Always Visible) --}}
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Seat Type</dt>
                <dd class="text-sm font-medium">{{ $seatType }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Has Ejection</dt>
                <dd class="text-sm font-medium">{{ $hasEjectionData ? 'Yes' : 'No' }}</dd>
            </div>
            @if (data_get($ejection, 'ejection_loop_time') !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ejection Time</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($ejection, 'ejection_loop_time'), 's', 2) }}</dd>
                </div>
            @endif
        </dl>

        {{-- Secondary Data (Collapsible, default open) --}}
        @if ($hasYaw || $hasPitch)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Axis Limits
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Set Yaw/Pitch Limits</dt>
                            <dd class="text-sm font-medium">{{ $setYawPitchLimits ? 'Yes' : 'No' }}</dd>
                        </div>
                        @if ($hasYaw)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Yaw</dt>
                                <dd class="text-sm font-medium">{{ fmt_range($yawMin, $yawMax, 'deg', 2) }}</dd>
                            </div>
                        @endif
                        @if ($hasPitch)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pitch</dt>
                                <dd class="text-sm font-medium">{{ fmt_range($pitchMin, $pitchMax, 'deg', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        {{-- Tertiary Data (Collapsible, default closed) --}}
        @if ($hasEjection && $hasEjectionData && $showTertiary)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Ejection
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                        @if (data_get($ejection, 'max_linear_velocity') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Linear Velocity</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($ejection, 'max_linear_velocity'), 'm/s', 2) }}</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_linear_acceleration') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Linear Acceleration</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($ejection, 'max_linear_acceleration'), 'm/s²', 2) }}</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_angular_velocity') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Angular Velocity</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($ejection, 'max_angular_velocity'), 'rad/s', 2) }}</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_angular_acceleration') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Angular Acceleration</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($ejection, 'max_angular_acceleration'), 'rad/s²', 2) }}</dd>
                            </div>
                        @endif

                    </dl>
                </div>
            </details>
        @endif
    </div>
</div>
