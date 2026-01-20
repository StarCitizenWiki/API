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

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="drone" class="size-4 text-primary" />
            <span>Flight Controller Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($scmSpeed !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">SCM Speed</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$scmSpeed, 0) }} m/s</dd>
                </div>
            @endif
            @if ($boostSpeedForward !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Boost Speed Forward</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$boostSpeedForward, 0) }} m/s</dd>
                </div>
            @endif
            @if ($boostSpeedBackward !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Boost Speed Backward</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$boostSpeedBackward, 0) }} m/s</dd>
                </div>
            @endif
            @if ($maxSpeed !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Speed</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$maxSpeed, 0) }} m/s</dd>
                </div>
            @endif
            @if ($pitch !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pitch</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$pitch, 0) }} deg/s</dd>
                </div>
            @endif
            @if ($yaw !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Yaw</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$yaw, 0) }} deg/s</dd>
                </div>
            @endif
            @if ($roll !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Roll</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$roll, 0) }} deg/s</dd>
                </div>
            @endif
            @if ($pitchBoosted !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pitch Boosted</dt>
                    <dd class="text-sm font-medium">{{ (int)$pitchBoosted }} deg/s</dd>
                </div>
            @endif
            @if ($yawBoosted !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Yaw Boosted</dt>
                    <dd class="text-sm font-medium">{{ (int)$yawBoosted }} deg/s</dd>
                </div>
            @endif
            @if ($rollBoosted !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Roll Boosted</dt>
                    <dd class="text-sm font-medium">{{ (int)$rollBoosted }} deg/s</dd>
                </div>
            @endif
        </dl>

        @if ($hasBoostCapacitor)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Boost Capacitor</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($bcCapacity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bcCapacity, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bcThresholdRatio !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Threshold Ratio</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bcThresholdRatio, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bcIdleCost !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Idle Cost</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bcIdleCost, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bcLinearCost !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Linear Cost</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bcLinearCost, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bcAngularCost !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Angular Cost</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bcAngularCost, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bcRegenPerSec !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Per Sec</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bcRegenPerSec, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bcRegenDelay !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Delay</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bcRegenDelay, 2) }}s</dd>
                            </div>
                        @endif
                        @if ($bcRegenTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bcRegenTime, 2) }}s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasBoostActivation)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Boost Activation</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($baPreDelayTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pre Delay Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baPreDelayTime, 2) }}s</dd>
                            </div>
                        @endif
                        @if ($baRampUpTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ramp Up Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baRampUpTime, 2) }}s</dd>
                            </div>
                        @endif
                        @if ($baRampDownTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ramp Down Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$baRampDownTime, 2) }}s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasThrusterDecay)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Thruster Decay</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($tdLinearAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Linear Accel</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tdLinearAccel, 2) }}</dd>
                            </div>
                        @endif
                        @if ($tdAngularAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Angular Accel</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tdAngularAccel, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasMultiplier)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Multipliers</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($mTorqueImbalance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Torque Imbalance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$mTorqueImbalance, 2) }}</dd>
                            </div>
                        @endif
                        @if ($mLift !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lift</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$mLift, 2) }}</dd>
                            </div>
                        @endif
                        @if ($mDrag !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Drag</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$mDrag, 2) }}</dd>
                            </div>
                        @endif
                        @if ($mScmMaxDrag !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">SCM Max Drag</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$mScmMaxDrag, 2) }}</dd>
                            </div>
                        @endif
                        @if ($mPrecisionLanding !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Precision Landing</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$mPrecisionLanding, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasBoostMultiplier)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Boost Multipliers</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($bmAccelXPos !== null || $bmAccelXNeg !== null)
                            <div class="space-y-1 sm:col-span-2">
                                <h3 class="mb-3 text-sm font-semibold">Accel X</h3>
                                <dl class="grid gap-4 sm:grid-cols-2">
                                    @if ($bmAccelXPos !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Positive</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)$bmAccelXPos, 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($bmAccelXNeg !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Negative</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)$bmAccelXNeg, 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                        @if ($bmAccelYPos !== null || $bmAccelYNeg !== null)
                            <div class="space-y-1 sm:col-span-2">
                                <h3 class="mb-3 text-sm font-semibold">Accel Y</h3>
                                <dl class="grid gap-4 sm:grid-cols-2">
                                    @if ($bmAccelYPos !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Positive</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)$bmAccelYPos, 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($bmAccelYNeg !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Negative</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)$bmAccelYNeg, 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                        @if ($bmAccelZPos !== null || $bmAccelZNeg !== null)
                            <div class="space-y-1 sm:col-span-2">
                                <h3 class="mb-3 text-sm font-semibold">Accel Z</h3>
                                <dl class="grid gap-4 sm:grid-cols-2">
                                    @if ($bmAccelZPos !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Positive</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)$bmAccelZPos, 2) }}</dd>
                                        </div>
                                    @endif
                                    @if ($bmAccelZNeg !== null)
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Negative</dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)$bmAccelZNeg, 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                        @if ($bmPitch !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pitch</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bmPitch, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bmYaw !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Yaw</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bmYaw, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bmRoll !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Roll</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bmRoll, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bmPitchAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pitch Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bmPitchAccel, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bmYawAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Yaw Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bmYawAccel, 2) }}</dd>
                            </div>
                        @endif
                        @if ($bmRollAccel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Roll Acceleration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$bmRollAccel, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasPrecisionMode)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Precision Mode</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($pmMaxSpeedFullProximityAssist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Speed Full Proximity Assist</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pmMaxSpeedFullProximityAssist, 0) }} m/s</dd>
                            </div>
                        @endif
                        @if ($pmMaxSpeedZeroProximityAssist !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Speed Zero Proximity Assist</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pmMaxSpeedZeroProximityAssist, 0) }} m/s</dd>
                            </div>
                        @endif
                        @if ($pmMinDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pmMinDistance, 0) }} m</dd>
                            </div>
                        @endif
                        @if ($pmMaxDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$pmMaxDistance, 0) }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasRecallParams)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Recall Params</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($rpHoverHeight !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Hover Height at Destination</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rpHoverHeight, 0) }} m</dd>
                            </div>
                        @endif
                        @if ($rpForwardOffset !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Forward Offset</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rpForwardOffset, 0) }} m</dd>
                            </div>
                        @endif
                        @if ($rpObstructionDetection !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Obstruction Detection Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rpObstructionDetection, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($rpDefaultPlatformDetection !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Default Platform Detection Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rpDefaultPlatformDetection, 0) }} m</dd>
                            </div>
                        @endif
                        @if ($rpMinRecallDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Recall Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rpMinRecallDistance, 0) }} m</dd>
                            </div>
                        @endif
                        @if ($rpBrakingDistanceOffset !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Braking Distance Offset</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$rpBrakingDistanceOffset, 0) }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasCollisionDetection)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Collision Detection</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($cdWarnSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Collision Warn Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cdWarnSpeed, 0) }} m/s</dd>
                            </div>
                        @endif
                        @if ($cdWarnTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Collision Warn Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cdWarnTime, 0) }} s</dd>
                            </div>
                        @endif
                        @if ($cdDangerCloseWarnTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Collision Danger Close Warn Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$cdDangerCloseWarnTime, 0) }} s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasGravlev)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Gravlev</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($gMaxSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gMaxSpeed, 0) }} m/s</dd>
                            </div>
                        @endif
                        @if ($gTurnFriction !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Turn Friction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gTurnFriction, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gAirControllerMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Air Controller Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gAirControllerMultiplier, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gAntiFallMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Anti Fall Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gAntiFallMultiplier, 2) }}</dd>
                            </div>
                        @endif
                        @if ($gLateralStrafeMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lateral Strafe Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$gLateralStrafeMultiplier, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
