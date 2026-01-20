@props([
    'tractorBeam',
])

@php
    $forceMin = data_get($tractorBeam, 'force.min');
    $forceMax = data_get($tractorBeam, 'force.max');
    $forceMaxVolume = data_get($tractorBeam, 'force.max_volume');
    $forceVolumeCoeff = data_get($tractorBeam, 'force.volume_force_coefficient');

    $rangeMin = data_get($tractorBeam, 'range.min');
    $rangeMax = data_get($tractorBeam, 'range.max');
    $rangeFullStrength = data_get($tractorBeam, 'range.full_strength_distance');
    $rangeMaxAngle = data_get($tractorBeam, 'range.max_angle');
    $rangeHitRadius = data_get($tractorBeam, 'range.hit_radius');

    $tetherBreakTime = data_get($tractorBeam, 'tether.tether_break_time');
    $tetherSafeRangeFactor = data_get($tractorBeam, 'tether.safe_range_value_factor');
    $tetherAllowScrolling = data_get($tractorBeam, 'tether.allow_scrolling_into_breaking_range');

    $cargoMinForce = data_get($tractorBeam, 'cargo_mode_override.min_force');
    $cargoMaxForce = data_get($tractorBeam, 'cargo_mode_override.max_force');
    $cargoMinAccel = data_get($tractorBeam, 'cargo_mode_override.min_acceleration');
    $cargoMaxAccel = data_get($tractorBeam, 'cargo_mode_override.max_acceleration');
    $cargoMinSpeed = data_get($tractorBeam, 'cargo_mode_override.min_speed');
    $cargoMaxSpeed = data_get($tractorBeam, 'cargo_mode_override.max_speed');
    $cargoAccelFactor = data_get($tractorBeam, 'cargo_mode_override.acceleration_factor');
    $cargoDegreesPerAction = data_get($tractorBeam, 'cargo_mode_override.degrees_per_action');
    $cargoMaxAngularAccel = data_get($tractorBeam, 'cargo_mode_override.max_angular_acceleration');
    $cargoMaxAngularVel = data_get($tractorBeam, 'cargo_mode_override.max_angular_velocity');
    $cargoDegreesScrollWheel = data_get($tractorBeam, 'cargo_mode_override.degrees_per_action_scroll_wheel');
    $cargoForceFractionRot = data_get($tractorBeam, 'cargo_mode_override.force_fraction_rotation');
    $cargoMinDistance = data_get($tractorBeam, 'cargo_mode_override.min_distance');
    $cargoMaxDistance = data_get($tractorBeam, 'cargo_mode_override.max_distance');
    $cargoFullStrengthDistance = data_get($tractorBeam, 'cargo_mode_override.full_strength_distance');

    $towingForce = data_get($tractorBeam, 'towing.force');
    $towingMaxAccel = data_get($tractorBeam, 'towing.max_acceleration');
    $towingMaxDist = data_get($tractorBeam, 'towing.max_distance');
    $towingQtMassLimit = data_get($tractorBeam, 'towing.qt_mass_limit');

    $hasForce = $forceMin !== null || $forceMax !== null || $forceMaxVolume !== null || $forceVolumeCoeff !== null;
    $hasRange = $rangeMin !== null || $rangeMax !== null || $rangeFullStrength !== null || $rangeMaxAngle !== null || $rangeHitRadius !== null;
    $hasTether = $tetherBreakTime !== null || $tetherSafeRangeFactor !== null || $tetherAllowScrolling !== null;
    $hasCargo = $cargoMinForce !== null || $cargoMaxForce !== null || $cargoMinAccel !== null || $cargoMaxAccel !== null || $cargoMinSpeed !== null || $cargoMaxSpeed !== null || $cargoAccelFactor !== null || $cargoDegreesPerAction !== null || $cargoMaxAngularAccel !== null || $cargoMaxAngularVel !== null || $cargoDegreesScrollWheel !== null || $cargoForceFractionRot !== null || $cargoMinDistance !== null || $cargoMaxDistance !== null || $cargoFullStrengthDistance !== null;
    $hasTowing = $towingForce !== null || $towingMaxAccel !== null || $towingMaxDist !== null || $towingQtMassLimit !== null;
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="magnet" class="size-4 text-primary" />
            <span>Tractor Beam Specifications</span>
        </h2>

        @if ($hasForce)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Force</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($forceMin !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Force</dt>
                                <dd class="text-sm font-medium">{{ number_format((int)$forceMin) }}N</dd>
                            </div>
                        @endif
                        @if ($forceMax !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Force</dt>
                                <dd class="text-sm font-medium">{{ number_format((int)$forceMax) }}N</dd>
                            </div>
                        @endif
                        @if ($forceMaxVolume !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Volume</dt>
                                <dd class="text-sm font-medium">{{ (int)$forceMaxVolume }}µSCU</dd>
                            </div>
                        @endif
                        @if ($forceVolumeCoeff !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Volume Force Efficient</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$forceVolumeCoeff, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasRange)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Range</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($rangeMin !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rangeMin, 2) }}m</dd>
                            </div>
                        @endif
                        @if ($rangeMax !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rangeMax, 2) }}m</dd>
                            </div>
                        @endif
                        @if ($rangeFullStrength !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Full Strength Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rangeFullStrength, 2) }}m</dd>
                            </div>
                        @endif
                        @if ($rangeMaxAngle !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Angle</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rangeMaxAngle, 2) }}°</dd>
                            </div>
                        @endif
                        @if ($rangeHitRadius !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Hit Radius</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rangeHitRadius, 2) }}m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasTether)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Tether</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($tetherBreakTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tether Break Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tetherBreakTime, 2) }}s</dd>
                            </div>
                        @endif
                        @if ($tetherSafeRangeFactor !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Safe Range Factor</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tetherSafeRangeFactor, 2) }}</dd>
                            </div>
                        @endif
                        @if ($tetherAllowScrolling !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Allow Scrolling Into Breaking Range</dt>
                                <dd class="text-sm font-medium">{{ $tetherAllowScrolling ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasCargo)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Cargo Mode Override</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($cargoMinForce !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Force</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMinForce, 2) }}N</dd>
                            </div>
                        @endif
                        @if ($cargoMaxForce !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Force</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMaxForce, 2) }}N</dd>
                            </div>
                        @endif
                        @if ($cargoMinAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMinAccel, 2) }}m/s²</dd>
                            </div>
                        @endif
                        @if ($cargoMaxAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMaxAccel, 2) }}m/s²</dd>
                            </div>
                        @endif
                        @if ($cargoMinSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMinSpeed, 2) }}m/s</dd>
                            </div>
                        @endif
                        @if ($cargoMaxSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMaxSpeed, 2) }}m/s</dd>
                            </div>
                        @endif
                        @if ($cargoAccelFactor !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Acceleration Factor</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoAccelFactor, 2) }}</dd>
                            </div>
                        @endif
                        @if ($cargoDegreesPerAction !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Degrees Per Action</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoDegreesPerAction, 2) }}°</dd>
                            </div>
                        @endif
                        @if ($cargoMaxAngularAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Angular Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMaxAngularAccel, 2) }}deg/s²</dd>
                            </div>
                        @endif
                        @if ($cargoMaxAngularVel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Angular Velocity</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMaxAngularVel, 2) }}deg/s</dd>
                            </div>
                        @endif
                        @if ($cargoDegreesScrollWheel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Degrees Per Action Scroll Wheel</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoDegreesScrollWheel, 2) }}°</dd>
                            </div>
                        @endif
                        @if ($cargoForceFractionRot !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Force Fraction Rotation</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoForceFractionRot, 2) }}</dd>
                            </div>
                        @endif
                        @if ($cargoMinDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMinDistance, 2) }}m</dd>
                            </div>
                        @endif
                        @if ($cargoMaxDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoMaxDistance, 2) }}m</dd>
                            </div>
                        @endif
                        @if ($cargoFullStrengthDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Full Strength Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cargoFullStrengthDistance, 2) }}m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasTowing)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Towing</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($towingForce !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Force</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$towingForce, 2) }}N</dd>
                            </div>
                        @endif
                        @if ($towingMaxAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$towingMaxAccel, 2) }}m/s²</dd>
                            </div>
                        @endif
                        @if ($towingMaxDist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$towingMaxDist, 2) }}m</dd>
                            </div>
                        @endif
                        @if ($towingQtMassLimit !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">QT Mass Limit</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$towingQtMassLimit, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
