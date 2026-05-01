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

    // Check visibility for sections
    $hasSecondary = $forceVolumeCoeff !== null
        || $rangeFullStrength !== null
        || $rangeMaxAngle !== null
        || $rangeHitRadius !== null
        || $tetherBreakTime !== null
        || $tetherSafeRangeFactor !== null
        || $tetherAllowScrolling !== null;

    $hasCargo = $cargoMinForce !== null
        || $cargoMaxForce !== null
        || $cargoMinAccel !== null
        || $cargoMaxAccel !== null
        || $cargoMinSpeed !== null
        || $cargoMaxSpeed !== null
        || $cargoAccelFactor !== null
        || $cargoDegreesPerAction !== null
        || $cargoMaxAngularAccel !== null
        || $cargoMaxAngularVel !== null
        || $cargoDegreesScrollWheel !== null
        || $cargoForceFractionRot !== null
        || $cargoMinDistance !== null
        || $cargoMaxDistance !== null
        || $cargoFullStrengthDistance !== null;

    $hasTowing = $towingForce !== null
        || $towingMaxAccel !== null
        || $towingMaxDist !== null
        || $towingQtMassLimit !== null;

    $hasTertiary = $hasCargo || $hasTowing;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Tractor Beam</h2>

        <!-- Primary Data (Always Visible) -->
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Force</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_range($forceMin, $forceMax, 'N', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Range</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_range($rangeMin, $rangeMax, 'm', 1) }}</dd>
            </div>
            @if ($forceMaxVolume !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Volume</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($forceMaxVolume, 'µSCU', 0) }}</dd>
                </div>
            @endif
        </dl>

        <!-- Secondary Data (Collapsible, expanded by default) -->
        @if ($hasTowing)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Towing
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($towingForce !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Force</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($towingForce, 'N', 1) }}</dd>
                            </div>
                        @endif
                        @if ($towingMaxAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Acceleration</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($towingMaxAccel, 'm/s²', 1) }}</dd>
                            </div>
                        @endif
                        @if ($towingMaxDist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Distance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($towingMaxDist, 'm', 1) }}</dd>
                            </div>
                        @endif
                        @if ($towingQtMassLimit !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">QT Mass Limit</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($towingQtMassLimit, '', 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        <!-- Secondary Data (Collapsible, expanded by default) -->
        @if ($hasSecondary)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Additional Specifications
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($forceVolumeCoeff !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Volume Force Coefficient</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($forceVolumeCoeff, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($rangeFullStrength !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Full Strength Distance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rangeFullStrength, 'm', 2) }}</dd>
                            </div>
                        @endif
                        @if ($rangeMaxAngle !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Angle</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rangeMaxAngle, '°', 2) }}</dd>
                            </div>
                        @endif
                        @if ($rangeHitRadius !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Hit Radius</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rangeHitRadius, 'm', 2) }}</dd>
                            </div>
                        @endif
                        @if ($tetherBreakTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Tether Break Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($tetherBreakTime, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($tetherSafeRangeFactor !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Safe Range Factor</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($tetherSafeRangeFactor, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($tetherAllowScrolling !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Allow Scrolling Into Breaking Range</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $tetherAllowScrolling ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        <!-- Tertiary Data (Collapsible, collapsed by default) -->
        @if ($hasTertiary)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Cargo Mode Overrides
                </summary>
                    @if ($hasCargo)
                        <dl class="mb-4 grid gap-3 grid-cols-2 sm:grid-cols-2 pt-1 pb-2">
                            @if ($cargoMinForce !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Min Force</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMinForce, 'N', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMaxForce !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Force</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMaxForce, 'N', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMinAccel !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Min Acceleration</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMinAccel, 'm/s²', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMaxAccel !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Acceleration</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMaxAccel, 'm/s²', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMinSpeed !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Min Speed</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMinSpeed, 'm/s', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMaxSpeed !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Speed</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMaxSpeed, 'm/s', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoAccelFactor !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Acceleration Factor</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoAccelFactor, '', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoDegreesPerAction !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Degrees Per Action</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoDegreesPerAction, '°', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMaxAngularAccel !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Angular Acceleration</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMaxAngularAccel, 'deg/s²', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMaxAngularVel !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Angular Velocity</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMaxAngularVel, 'deg/s', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoDegreesScrollWheel !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Degrees Per Action Scroll Wheel</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoDegreesScrollWheel, '°', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoForceFractionRot !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Force Fraction Rotation</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoForceFractionRot, '', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMinDistance !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Min Distance</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMinDistance, 'm', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoMaxDistance !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Distance</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoMaxDistance, 'm', 2) }}</dd>
                                </div>
                            @endif
                            @if ($cargoFullStrengthDistance !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Full Strength Distance</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cargoFullStrengthDistance, 'm', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
            </details>
        @endif
    </div>
</div>
