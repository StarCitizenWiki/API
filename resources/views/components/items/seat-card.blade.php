@props([
    'seat',
])

@php
    $seatType = data_get($seat, 'seat_type');
    $setYawPitchLimits = data_get($seat, 'set_yaw_pitch_limits');

    $yaw = data_get($seat, 'yaw', []);
    $yawMin = data_get($yaw, 'minimum');
    $yawMax = data_get($yaw, 'maximum');
    $hasYaw = is_array($yaw) && array_filter($yaw, fn($v) => $v !== null);

    $pitch = data_get($seat, 'pitch', []);
    $pitchMin = data_get($pitch, 'minimum');
    $pitchMax = data_get($pitch, 'maximum');
    $hasPitch = is_array($pitch) && array_filter($pitch, fn($v) => $v !== null);

    $hasEjection = data_get($seat, 'has_ejection');
    $ejection = data_get($seat, 'ejection', []);
    $hasEjectionData = is_array($ejection) && $ejection !== [];
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="chair" class="size-4 text-primary" />
            <span>Seat Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($seatType !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Seat Type</dt>
                    <dd class="text-sm font-medium">{{ $seatType }}</dd>
                </div>
            @endif
            @if ($setYawPitchLimits !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Set Yaw/Pitch Limits</dt>
                    <dd class="text-sm font-medium">{{ $setYawPitchLimits ? 'Yes' : 'No' }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasYaw || $hasPitch)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Axis Limits</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($hasYaw)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Yaw</dt>
                                <dd class="text-sm font-medium">
                                    {{ number_format((float)$yawMin, 2) }}° - {{ number_format((float)$yawMax, 2) }}°
                                </dd>
                            </div>
                        @endif
                        @if ($hasPitch)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pitch</dt>
                                <dd class="text-sm font-medium">
                                    {{ number_format((float)$pitchMin, 2) }}° - {{ number_format((float)$pitchMax, 2) }}°
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasEjection && $hasEjectionData)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Ejection</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($ejection, 'max_linear_velocity') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Linear Velocity</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($ejection, 'max_linear_velocity'), 2) }} m/s</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_linear_acceleration') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Linear Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($ejection, 'max_linear_acceleration'), 2) }} m/s²</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_angular_velocity') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Angular Velocity</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($ejection, 'max_angular_velocity'), 2) }} rad/s</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'max_angular_acceleration') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Angular Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($ejection, 'max_angular_acceleration'), 2) }} rad/s²</dd>
                            </div>
                        @endif
                        @if (data_get($ejection, 'ejection_loop_time') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ejection Loop Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($ejection, 'ejection_loop_time'), 2) }} s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
