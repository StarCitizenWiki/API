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

    $hasYawAxis = collect($yawAxis)->filter()->isNotEmpty();
    $hasPitchAxis = collect($pitchAxis)->filter()->isNotEmpty();
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="circle-plus" class="size-4 text-primary" />
            <span>Turret Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($rotationStyle !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Rotation Style</dt>
                    <dd class="text-sm font-medium">{{ $rotationStyle }}</dd>
                </div>
            @endif
            @if ($mounts !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mounts</dt>
                    <dd class="text-sm font-medium">{{ (int)$mounts }}</dd>
                </div>
            @endif
            @if ($minSize !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Size</dt>
                    <dd class="text-sm font-medium">{{ (int)$minSize }}</dd>
                </div>
            @endif
            @if ($maxSize !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Size</dt>
                    <dd class="text-sm font-medium">{{ (int)$maxSize }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasYawAxis)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Yaw Axis</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($yawSlavedOnly !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Slaved Only</dt>
                                <dd class="text-sm font-medium">{{ $yawSlavedOnly ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($yawSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$yawSpeed, 2) }} deg/s</dd>
                            </div>
                        @endif
                        @if ($yawTimeToFullSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Time to Full Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$yawTimeToFullSpeed, 2) }}s</dd>
                            </div>
                        @endif
                        @if ($yawAccelDecay !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Acceleration Decay</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$yawAccelDecay, 2) }}</dd>
                            </div>
                        @endif
                        @if ($yawAngleMin !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Angle Limit Min</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$yawAngleMin, 2) }} degrees</dd>
                            </div>
                        @endif
                        @if ($yawAngleMax !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Angle Limit Max</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$yawAngleMax, 2) }} degrees</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasPitchAxis)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Pitch Axis</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($pitchSlavedOnly !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Slaved Only</dt>
                                <dd class="text-sm font-medium">{{ $pitchSlavedOnly ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($pitchSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pitchSpeed, 2) }} deg/s</dd>
                            </div>
                        @endif
                        @if ($pitchTimeToFullSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Time to Full Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pitchTimeToFullSpeed, 2) }}s</dd>
                            </div>
                        @endif
                        @if ($pitchAccelDecay !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Acceleration Decay</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pitchAccelDecay, 2) }}</dd>
                            </div>
                        @endif
                        @if ($pitchAngleMin !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Angle Limit Min</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pitchAngleMin, 2) }} degrees</dd>
                            </div>
                        @endif
                        @if ($pitchAngleMax !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Angle Limit Max</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pitchAngleMax, 2) }} degrees</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
