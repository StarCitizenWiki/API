@props([
    'missile',
])

@php
    $signalType = data_get($missile, 'signal_type');
    $trackingSignalMin = data_get($missile, 'tracking_signal_min');
    $clusterSize = data_get($missile, 'cluster_size');

    $flight = data_get($missile, 'flight', []);
    $flightEnableLifetime = data_get($flight, 'enable_lifetime');
    $flightMaxLifetime = data_get($flight, 'max_lifetime');
    $flightRange = data_get($flight, 'range');
    $flightSpeed = data_get($flight, 'speed');
    $flightBoostSpeed = data_get($flight, 'boost_speed');
    $flightInterceptSpeed = data_get($flight, 'intercept_speed');
    $flightTerminalSpeed = data_get($flight, 'terminal_speed');
    $flightFuelTankSize = data_get($flight, 'fuel_tank_size');
    $flightBoostPhaseDuration = data_get($flight, 'boost_phase_duration');
    $flightTerminalPhaseEngagementTime = data_get($flight, 'terminal_phase_engagement_time');
    $flightTerminalPhaseEngagementAngle = data_get($flight, 'terminal_phase_engagement_angle');
    $hasFlight = is_array($flight) && array_filter($flight, fn($v) => $v !== null);

    $targetLock = data_get($missile, 'target_lock', []);
    $tlSignalResilienceMin = data_get($targetLock, 'signal_resilience_min');
    $tlSignalResilienceMax = data_get($targetLock, 'signal_resilience_max');
    $tlRangeMax = data_get($targetLock, 'range_max');
    $tlRangeMin = data_get($targetLock, 'range_min');
    $tlAngle = data_get($targetLock, 'angle');
    $tlSignalAmplifier = data_get($targetLock, 'signal_amplifier');
    $tlIncreaseRate = data_get($targetLock, 'increase_rate');
    $tlAllowDumbFiring = data_get($targetLock, 'allow_dumb_firing');
    $hasTargetLock = is_array($targetLock) && array_filter($targetLock, fn($v) => $v !== null);

    $explosion = data_get($missile, 'explosion', []);
    $expIsCluster = data_get($explosion, 'is_cluster');
    $expClusterSize = data_get($explosion, 'cluster_size');
    $expRequiresLauncher = data_get($explosion, 'requires_launcher');
    $expAllowDumbFiring = data_get($explosion, 'allow_dumb_firing');
    $expRadiusMin = data_get($explosion, 'radius_min');
    $expRadiusMax = data_get($explosion, 'radius_max');
    $expSafetyDistance = data_get($explosion, 'safety_distance');
    $expProximity = data_get($explosion, 'proximity');
    $hasExplosion = is_array($explosion) && array_filter($explosion, fn($v) => $v !== null);

    $delays = data_get($missile, 'delays', []);
    $delArmTime = data_get($delays, 'arm_time');
    $delIgniteTime = data_get($delays, 'ignite_time');
    $delCollisionDelayTime = data_get($delays, 'collision_delay_time');
    $delLockTime = data_get($delays, 'lock_time');
    $hasDelays = is_array($delays) && array_filter($delays, fn($v) => $v !== null);

    $damageTotal = data_get($missile, 'damage_total');
    $damageMap = data_get($missile, 'damage_map', []);
    $hasDamageMap = is_array($damageMap) && $damageMap !== [];
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="rocket" class="size-4 text-primary" />
            <span>Missile Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($signalType !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Signal Type</dt>
                    <dd class="text-sm font-medium">{{ $signalType }}</dd>
                </div>
            @endif
            @if ($trackingSignalMin !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tracking Signal Min</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$trackingSignalMin, 2) }}</dd>
                </div>
            @endif
            @if ($clusterSize !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cluster Size</dt>
                    <dd class="text-sm font-medium">{{ (int)$clusterSize }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasFlight)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Flight Performance</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($flightEnableLifetime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Enable Lifetime</dt>
                                <dd class="text-sm font-medium">{{ $flightEnableLifetime ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($flightMaxLifetime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Lifetime</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightMaxLifetime, 2) }} s</dd>
                            </div>
                        @endif
                        @if ($flightRange !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightRange, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($flightSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightSpeed, 2) }} m/s</dd>
                            </div>
                        @endif
                        @if ($flightBoostSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Boost Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightBoostSpeed, 2) }} m/s</dd>
                            </div>
                        @endif
                        @if ($flightInterceptSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Intercept Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightInterceptSpeed, 2) }} m/s</dd>
                            </div>
                        @endif
                        @if ($flightTerminalSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Terminal Speed</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightTerminalSpeed, 2) }} m/s</dd>
                            </div>
                        @endif
                        @if ($flightFuelTankSize !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Tank Size</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightFuelTankSize, 2) }}</dd>
                            </div>
                        @endif
                        @if ($flightBoostPhaseDuration !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Boost Phase Duration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightBoostPhaseDuration, 2) }} s</dd>
                            </div>
                        @endif
                        @if ($flightTerminalPhaseEngagementTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Terminal Phase Engagement Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightTerminalPhaseEngagementTime, 2) }} s</dd>
                            </div>
                        @endif
                        @if ($flightTerminalPhaseEngagementAngle !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Terminal Phase Engagement Angle</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$flightTerminalPhaseEngagementAngle, 2) }} deg</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasTargetLock)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Target Lock</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($tlRangeMin !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range Min</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tlRangeMin, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($tlRangeMax !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range Max</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tlRangeMax, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($tlAngle !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lock Angle</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tlAngle, 2) }} deg</dd>
                            </div>
                        @endif
                        @if ($tlSignalResilienceMin !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Signal Resilience Min</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tlSignalResilienceMin, 2) }}</dd>
                            </div>
                        @endif
                        @if ($tlSignalResilienceMax !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Signal Resilience Max</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tlSignalResilienceMax, 2) }}</dd>
                            </div>
                        @endif
                        @if ($tlSignalAmplifier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Signal Amplifier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tlSignalAmplifier, 2) }}</dd>
                            </div>
                        @endif
                        @if ($tlIncreaseRate !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lock Increase Rate</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$tlIncreaseRate, 2) }} /s</dd>
                            </div>
                        @endif
                        @if ($tlAllowDumbFiring !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Allow Dumb Firing</dt>
                                <dd class="text-sm font-medium">{{ $tlAllowDumbFiring ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasExplosion)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Explosion</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($expIsCluster !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Is Cluster</dt>
                                <dd class="text-sm font-medium">{{ $expIsCluster ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($expClusterSize !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cluster Size</dt>
                                <dd class="text-sm font-medium">{{ (int)$expClusterSize }}</dd>
                            </div>
                        @endif
                        @if ($expRequiresLauncher !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Requires Launcher</dt>
                                <dd class="text-sm font-medium">{{ $expRequiresLauncher ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($expAllowDumbFiring !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Allow Dumb Firing</dt>
                                <dd class="text-sm font-medium">{{ $expAllowDumbFiring ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($expRadiusMin !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radius Min</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$expRadiusMin, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($expRadiusMax !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radius Max</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$expRadiusMax, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($expSafetyDistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Safety Distance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$expSafetyDistance, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($expProximity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Proximity</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$expProximity, 2) }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasDelays)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Delays</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($delArmTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Arm Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$delArmTime, 2) }} s</dd>
                            </div>
                        @endif
                        @if ($delIgniteTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ignite Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$delIgniteTime, 2) }} s</dd>
                            </div>
                        @endif
                        @if ($delCollisionDelayTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Collision Delay Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$delCollisionDelayTime, 2) }} s</dd>
                            </div>
                        @endif
                        @if ($delLockTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lock Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$delLockTime, 2) }} s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($damageTotal !== null || $hasDamageMap)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Damage</div>
                <div class="collapse-content">
                    @if ($damageTotal !== null)
                        <dl class="grid gap-4 sm:grid-cols-2 mb-4">
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage Total</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$damageTotal, 2) }}</dd>
                            </div>
                        </dl>
                    @endif
                    @if ($hasDamageMap)
                        <dl class="grid gap-4 sm:grid-cols-2">
                            @foreach ($damageMap as $type => $value)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($type) }}</dt>
                                    <dd class="text-sm font-medium">{{ number_format((float)$value, 2) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
