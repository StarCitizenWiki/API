@props([
    'turret',
 ])

@php
    $rotationStyle = data_get($turret, 'rotation_style');
    $mounts = data_get($turret, 'mounts');
    $minSize = data_get($turret, 'min_size');
    $maxSize = data_get($turret, 'max_size');

    $yawAxis = data_get($turret, 'yaw_axis', []);
    $yawSlavedOnly = data_get($yawAxis, 'slaved_only');
    $yawSpeed = data_get($yawAxis, 'speed');
    $yawTimeToFullSpeed = data_get($yawAxis, 'time_to_full_speed');
    $yawAccelDecay = data_get($yawAxis, 'acceleration_decay');
    $yawAngleMin = data_get($yawAxis, 'angle_limit_min');
    $yawAngleMax = data_get($yawAxis, 'angle_limit_max');

    $pitchAxis = data_get($turret, 'pitch_axis', []);
    $pitchSlavedOnly = data_get($pitchAxis, 'slaved_only');
    $pitchSpeed = data_get($pitchAxis, 'speed');
    $pitchTimeToFullSpeed = data_get($pitchAxis, 'time_to_full_speed');
    $pitchAccelDecay = data_get($pitchAxis, 'acceleration_decay');
    $pitchAngleMin = data_get($pitchAxis, 'angle_limit_min');
    $pitchAngleMax = data_get($pitchAxis, 'angle_limit_max');

    // Determine if sections have data to render
    $hasSecondaryData = $yawSpeed !== null || $yawTimeToFullSpeed !== null || $pitchSpeed !== null || $pitchTimeToFullSpeed !== null;

    $hasTertiaryData = $yawSlavedOnly !== null || $yawAccelDecay !== null || $yawAngleMin !== null || $yawAngleMax !== null
        || $pitchSlavedOnly !== null || $pitchAccelDecay !== null || $pitchAngleMin !== null || $pitchAngleMax !== null;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Turret</h2>

        {{-- Primary Data: Always Visible --}}
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($rotationStyle !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Rotation Style</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ $rotationStyle }}</dd>
                </div>
            @endif
            @if ($mounts !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Mounts</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($mounts) }}</dd>
                </div>
            @endif
            @if ($minSize !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Equippable Size</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_range($minSize, $maxSize, '') }}</dd>
                </div>
            @endif
        </dl>

        {{-- Secondary Data: Collapsible, Expanded by Default --}}
        @if ($hasSecondaryData)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Performance
                </summary>
                    @if ($yawSpeed !== null || $yawTimeToFullSpeed !== null || $pitchSpeed !== null || $pitchTimeToFullSpeed !== null)
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if ($yawSpeed !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Yaw Speed</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($yawSpeed, 'deg/s', 2) }}</dd>
                                </div>
                            @endif
                            @if ($yawTimeToFullSpeed !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Yaw Time to Full Speed</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($yawTimeToFullSpeed, 's', 2) }}</dd>
                                </div>
                            @endif
                            @if ($pitchSpeed !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pitch Speed</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pitchSpeed, 'deg/s', 2) }}</dd>
                                </div>
                            @endif
                            @if ($pitchTimeToFullSpeed !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pitch Time to Full Speed</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pitchTimeToFullSpeed, 's', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
            </details>
        @endif

        {{-- Tertiary Data: Collapsible, Collapsed by Default --}}
        @if ($hasTertiaryData)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Advanced
                </summary>
                    @if ($hasTertiaryData)
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if ($yawSlavedOnly !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Yaw Slaved Only</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ $yawSlavedOnly ? 'Yes' : 'No' }}</dd>
                                </div>
                            @endif
                            @if ($yawAccelDecay !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Yaw Acceleration Decay</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($yawAccelDecay, 2) }}</dd>
                                </div>
                            @endif
                            @if ($yawAngleMin !== null || $yawAngleMax !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Yaw Angle Limit</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_range($yawAngleMin, $yawAngleMax, 'deg', 2) }}</dd>
                                </div>
                            @endif
                            @if ($pitchSlavedOnly !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pitch Slaved Only</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ $pitchSlavedOnly ? 'Yes' : 'No' }}</dd>
                                </div>
                            @endif
                            @if ($pitchAccelDecay !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pitch Acceleration Decay</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($pitchAccelDecay, 2) }}</dd>
                                </div>
                            @endif
                            @if ($pitchAngleMin !== null || $pitchAngleMax !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pitch Angle Limit</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_range($pitchAngleMin, $pitchAngleMax, 'deg', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
            </details>
        @endif
    </div>
</div>
