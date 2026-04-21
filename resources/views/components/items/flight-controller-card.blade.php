@props([
    'flightController',
])

@php
    $scmSpeed = data_get($flightController, 'scm_speed');
    $boostSpeedForward = data_get($flightController, 'boost_speed_forward');
    $boostSpeedBackward = data_get($flightController, 'boost_speed_backward');
    $maxSpeed = data_get($flightController, 'max_speed');

    $pitch = data_get($flightController, 'pitch');
    $yaw = data_get($flightController, 'yaw');
    $roll = data_get($flightController, 'roll');

    $pitchBoosted = data_get($flightController, 'pitch_boosted');
    $yawBoosted = data_get($flightController, 'yaw_boosted');
    $rollBoosted = data_get($flightController, 'roll_boosted');

    $boostCapacitor = data_get($flightController, 'boost_capacitor', []);
    $bcCapacity = data_get($boostCapacitor, 'capacity');
    $bcThresholdRatio = data_get($boostCapacitor, 'threshold_ratio');
    $bcIdleCost = data_get($boostCapacitor, 'idle_cost');
    $bcLinearCost = data_get($boostCapacitor, 'linear_cost');
    $bcAngularCost = data_get($boostCapacitor, 'angular_cost');
    $bcRegenPerSec = data_get($boostCapacitor, 'regen_per_sec');
    $bcRegenDelay = data_get($boostCapacitor, 'regen_delay');
    $bcRegenTime = data_get($boostCapacitor, 'regen_time');

    $boostActivation = data_get($flightController, 'boost_activation', []);
    $baPreDelayTime = data_get($boostActivation, 'pre_delay_time');
    $baRampUpTime = data_get($boostActivation, 'ramp_up_time');
    $baRampDownTime = data_get($boostActivation, 'ramp_down_time');

    $thrusterDecay = data_get($flightController, 'thruster_decay', []);
    $tdLinearAccel = data_get($thrusterDecay, 'linear_accel');
    $tdAngularAccel = data_get($thrusterDecay, 'angular_accel');

    $multiplier = data_get($flightController, 'multiplier', []);
    $mTorqueImbalance = data_get($multiplier, 'torque_imbalance');
    $mLift = data_get($multiplier, 'lift');
    $mDrag = data_get($multiplier, 'drag');
    $mScmMaxDrag = data_get($multiplier, 'scm_max_drag');
    $mPrecisionLanding = data_get($multiplier, 'precision_landing');

    $boostMultiplier = data_get($flightController, 'boost_multiplier', []);
    $bmAccelX = data_get($boostMultiplier, 'accel_x', []);
    $bmAccelXPos = data_get($bmAccelX, 'positive');
    $bmAccelXNeg = data_get($bmAccelX, 'negative');
    $bmAccelY = data_get($boostMultiplier, 'accel_y', []);
    $bmAccelYPos = data_get($bmAccelY, 'positive');
    $bmAccelYNeg = data_get($bmAccelY, 'negative');
    $bmAccelZ = data_get($boostMultiplier, 'accel_z', []);
    $bmAccelZPos = data_get($bmAccelZ, 'positive');
    $bmAccelZNeg = data_get($bmAccelZ, 'negative');
    $bmPitch = data_get($boostMultiplier, 'pitch');
    $bmYaw = data_get($boostMultiplier, 'yaw');
    $bmRoll = data_get($boostMultiplier, 'roll');
    $bmPitchAccel = data_get($boostMultiplier, 'pitch_accel');
    $bmYawAccel = data_get($boostMultiplier, 'yaw_accel');
    $bmRollAccel = data_get($boostMultiplier, 'roll_accel');

    $precisionMode = data_get($flightController, 'precision_mode', []);
    $pmMaxSpeedFullProximityAssist = data_get($precisionMode, 'max_speed_full_proximity_assist');
    $pmMaxSpeedZeroProximityAssist = data_get($precisionMode, 'max_speed_zero_proximity_assist');
    $pmMinDistance = data_get($precisionMode, 'min_distance');
    $pmMaxDistance = data_get($precisionMode, 'max_distance');

    $recallParams = data_get($flightController, 'recall_params', []);
    $rpHoverHeight = data_get($recallParams, 'hover_height_at_destination');
    $rpForwardOffset = data_get($recallParams, 'forward_offset');
    $rpObstructionDetection = data_get($recallParams, 'obstruction_detection_range');
    $rpDefaultPlatformDetection = data_get($recallParams, 'default_platform_detection_range');
    $rpMinRecallDistance = data_get($recallParams, 'minimum_recall_distance');
    $rpBrakingDistanceOffset = data_get($recallParams, 'braking_distance_offset');

    $collisionDetection = data_get($flightController, 'collision_detection', []);
    $cdWarnSpeed = data_get($collisionDetection, 'collision_warn_speed');
    $cdWarnTime = data_get($collisionDetection, 'collision_warn_time');
    $cdDangerCloseWarnTime = data_get($collisionDetection, 'collision_danger_close_warn_time');

    $gravlev = data_get($flightController, 'gravlev', []);
    $gMaxSpeed = data_get($gravlev, 'max_speed');
    $gTurnFriction = data_get($gravlev, 'turn_friction');
    $gAirControllerMultiplier = data_get($gravlev, 'air_controller_multiplier');
    $gAntiFallMultiplier = data_get($gravlev, 'anti_fall_multiplier');
    $gLateralStrafeMultiplier = data_get($gravlev, 'lateral_strafe_multiplier');

    $hasBoostCapacitor = collect($boostCapacitor)->filter()->isNotEmpty();
    $hasBoostActivation = collect($boostActivation)->filter()->isNotEmpty();
    $hasThrusterDecay = collect($thrusterDecay)->filter()->isNotEmpty();
    $hasMultiplier = collect($multiplier)->filter()->isNotEmpty();
    $hasBoostMultiplier = collect($boostMultiplier)->filter()->isNotEmpty();
    $hasPrecisionMode = collect($precisionMode)->filter()->isNotEmpty();
    $hasRecallParams = $recallParams !== null;
    $hasCollisionDetection = $collisionDetection !== null;
    $hasGravlev = collect($gravlev)->filter()->isNotEmpty();
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Flight Controller</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">SCM Speed</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($scmSpeed, 'm/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Speed (Nav)</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($maxSpeed, 'm/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Boost Forward</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($boostSpeedForward, 'm/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Boost Backward</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($boostSpeedBackward, 'm/s', 0) }}</dd>
            </div>
        </dl>

        <dl class="mt-2 grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Pitch</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pitch, 'deg/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Yaw</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($yaw, 'deg/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Roll</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($roll, 'deg/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Pitch Boosted</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pitchBoosted, 'deg/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Yaw Boosted</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($yawBoosted, 'deg/s', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Roll Boosted</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rollBoosted, 'deg/s', 0) }}</dd>
            </div>
        </dl>

        {{-- Secondary Data (Collapsible, Default Open) --}}
        @if ($hasBoostCapacitor)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Boost Capacitor
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($bcRegenTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Regen Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($bcRegenTime, 's', 1) }}</dd>
                            </div>
                        @endif
                        @if ($bcRegenDelay !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Regen Delay</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($bcRegenDelay, 's', 1) }}</dd>
                            </div>
                        @endif
                            @if ($baRampUpTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Ramp Up Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baRampUpTime, 's', 1) }}</dd>
                                </div>
                            @endif
                    </dl>
            </details>
        @endif

        @if ($hasBoostActivation)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Boost Activation
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($baPreDelayTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Pre Delay Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baPreDelayTime, 's', 1) }}</dd>
                            </div>
                        @endif
                        @if ($baRampUpTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Ramp Up Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baRampUpTime, 's', 1) }}</dd>
                            </div>
                        @endif
                        @if ($baRampDownTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Ramp Down Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($baRampDownTime, 's', 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasThrusterDecay)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Thruster Decay
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($tdLinearAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Linear Accel</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($tdLinearAccel, 1) }}</dd>
                            </div>
                        @endif
                        @if ($tdAngularAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Angular Accel</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($tdAngularAccel, 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasMultiplier)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Multipliers
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($mTorqueImbalance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Torque Imbalance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($mTorqueImbalance, 1) }}</dd>
                            </div>
                        @endif
                        @if ($mLift !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Lift</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($mLift, 1) }}</dd>
                            </div>
                        @endif
                        @if ($mDrag !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Drag</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($mDrag, 1) }}</dd>
                            </div>
                        @endif
                        @if ($mScmMaxDrag !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">SCM Max Drag</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($mScmMaxDrag, 1) }}</dd>
                            </div>
                        @endif
                        @if ($mPrecisionLanding !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Precision Landing</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($mPrecisionLanding, 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        {{-- Tertiary Data (Collapsible, Default Closed) --}}
        @if ($hasBoostMultiplier)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Boost Multipliers
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($bmAccelXPos !== null || $bmAccelXNeg !== null)
                            <div class="space-y-1 sm:col-span-2">
                                <h4 class="mb-2 text-xs font-medium uppercase tracking-wide text-base-content/45">Accel X</h4>
                                <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                    @if ($bmAccelXPos !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Positive</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmAccelXPos, 1) }}</dd>
                                        </div>
                                    @endif
                                    @if ($bmAccelXNeg !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Negative</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmAccelXNeg, 1) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                        @if ($bmAccelYPos !== null || $bmAccelYNeg !== null)
                            <div class="space-y-1 sm:col-span-2">
                                <h4 class="mb-2 text-xs font-medium uppercase tracking-wide text-base-content/45">Accel Y</h4>
                                <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                    @if ($bmAccelYPos !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Positive</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmAccelYPos, 1) }}</dd>
                                        </div>
                                    @endif
                                    @if ($bmAccelYNeg !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Negative</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmAccelYNeg, 1) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                        @if ($bmAccelZPos !== null || $bmAccelZNeg !== null)
                            <div class="space-y-1 sm:col-span-2">
                                <h4 class="mb-2 text-xs font-medium uppercase tracking-wide text-base-content/45">Accel Z</h4>
                                <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                                    @if ($bmAccelZPos !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Positive</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmAccelZPos, 1) }}</dd>
                                        </div>
                                    @endif
                                    @if ($bmAccelZNeg !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Negative</dt>
                                            <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmAccelZNeg, 1) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    </dl>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 mt-4 pt-1 pb-2">
                        @if ($bmPitch !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Pitch</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmPitch, 1) }}</dd>
                            </div>
                        @endif
                        @if ($bmYaw !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Yaw</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmYaw, 1) }}</dd>
                            </div>
                        @endif
                        @if ($bmRoll !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Roll</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmRoll, 1) }}</dd>
                            </div>
                        @endif
                        @if ($bmPitchAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Pitch Accel</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmPitchAccel, 1) }}</dd>
                            </div>
                        @endif
                        @if ($bmYawAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Yaw Accel</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmYawAccel, 1) }}</dd>
                            </div>
                        @endif
                        @if ($bmRollAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Roll Accel</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($bmRollAccel, 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasPrecisionMode)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Precision Mode
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($pmMaxSpeedFullProximityAssist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Speed Full Proximity Assist</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pmMaxSpeedFullProximityAssist, 'm/s', 0) }}</dd>
                            </div>
                        @endif
                        @if ($pmMaxSpeedZeroProximityAssist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Speed Zero Proximity Assist</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pmMaxSpeedZeroProximityAssist, 'm/s', 0) }}</dd>
                            </div>
                        @endif
                        @if ($pmMinDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Min Distance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pmMinDistance, 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if ($pmMaxDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Distance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($pmMaxDistance, 'm', 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasRecallParams)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Recall Params
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($rpHoverHeight !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Hover Height at Destination</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rpHoverHeight, 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if ($rpForwardOffset !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Forward Offset</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rpForwardOffset, 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if ($rpObstructionDetection !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Obstruction Detection Range</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rpObstructionDetection, 'm', 1) }}</dd>
                            </div>
                        @endif
                        @if ($rpDefaultPlatformDetection !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Default Platform Detection Range</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rpDefaultPlatformDetection, 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if ($rpMinRecallDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Minimum Recall Distance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rpMinRecallDistance, 'm', 0) }}</dd>
                            </div>
                        @endif
                        @if ($rpBrakingDistanceOffset !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Braking Distance Offset</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($rpBrakingDistanceOffset, 'm', 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasCollisionDetection)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Collision Detection
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($cdWarnSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Collision Warn Speed</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cdWarnSpeed, 'm/s', 0) }}</dd>
                            </div>
                        @endif
                        @if ($cdWarnTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Collision Warn Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cdWarnTime, 's', 0) }}</dd>
                            </div>
                        @endif
                        @if ($cdDangerCloseWarnTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Collision Danger Close Warn Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($cdDangerCloseWarnTime, 's', 0) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasGravlev)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Gravlev
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($gMaxSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Speed</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($gMaxSpeed, 'm/s', 0) }}</dd>
                            </div>
                        @endif
                        @if ($gTurnFriction !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Turn Friction</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($gTurnFriction, 1) }}</dd>
                            </div>
                        @endif
                        @if ($gAirControllerMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Air Controller Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($gAirControllerMultiplier, 1) }}</dd>
                            </div>
                        @endif
                        @if ($gAntiFallMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Anti Fall Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($gAntiFallMultiplier, 1) }}</dd>
                            </div>
                        @endif
                        @if ($gLateralStrafeMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Lateral Strafe Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($gLateralStrafeMultiplier, 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif
    </div>
</div>
