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
    $hasFlight = is_array($flight) && collect($flight)->filter(fn($v) => $v !== null)->isNotEmpty();

    $targetLock = data_get($missile, 'target_lock', []);
    $tlSignalResilienceMin = data_get($targetLock, 'signal_resilience_min');
    $tlSignalResilienceMax = data_get($targetLock, 'signal_resilience_max');
    $tlRangeMax = data_get($targetLock, 'range_max');
    $tlRangeMin = data_get($targetLock, 'range_min');
    $tlAngle = data_get($targetLock, 'angle');
    $tlSignalAmplifier = data_get($targetLock, 'signal_amplifier');
    $tlIncreaseRate = data_get($targetLock, 'increase_rate');
    $tlAllowDumbFiring = data_get($targetLock, 'allow_dumb_firing');
    $hasTargetLock = is_array($targetLock) && collect($targetLock)->filter(fn($v) => $v !== null)->isNotEmpty();

    $explosion = data_get($missile, 'explosion', []);
    $expIsCluster = data_get($explosion, 'is_cluster');
    $expClusterSize = data_get($explosion, 'cluster_size');
    $expRequiresLauncher = data_get($explosion, 'requires_launcher');
    $expAllowDumbFiring = data_get($explosion, 'allow_dumb_firing');
    $expRadiusMin = data_get($explosion, 'radius_min');
    $expRadiusMax = data_get($explosion, 'radius_max');
    $expSafetyDistance = data_get($explosion, 'safety_distance');
    $expProximity = data_get($explosion, 'proximity');
    $hasExplosion = is_array($explosion) && collect($explosion)->filter(fn($v) => $v !== null)->isNotEmpty();

    $delays = data_get($missile, 'delays', []);
    $delArmTime = data_get($delays, 'arm_time');
    $delIgniteTime = data_get($delays, 'ignite_time');
    $delCollisionDelayTime = data_get($delays, 'collision_delay_time');
    $delLockTime = data_get($delays, 'lock_time');
    $hasDelays = is_array($delays) && collect($delays)->filter(fn($v) => $v !== null)->isNotEmpty();

    $damageTotal = data_get($missile, 'damage_total');
    $damageMap = data_get($missile, 'damage_map', []);
    $hasDamageMap = is_array($damageMap) && $damageMap !== [];
    $hasDamage = $damageTotal !== null || $hasDamageMap;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title flex items-center gap-2">
            <x-icon name="rocket" class="size-4 text-primary" />
            <span>Missile</span>
        </h2>

        {{-- Primary Data (Always Visible) --}}
        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 ">
        @if ($signalType !== null)
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Signal Type</dt>
                <dd class="text-sm font-medium">{{ $signalType }}</dd>
            </div>
        @endif
            @if ($damageTotal !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage Total</dt>
                    <dd class="text-sm font-medium text-info">{{ fmt_or_dash($damageTotal) }}</dd>
                </div>
            @endif
            @if ($flightRange !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightRange, 'm', 0) }}</dd>
                </div>
            @endif
            @if ($tlRangeMin !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lock Range</dt>
                    <dd class="text-sm font-medium">{{ fmt_range($tlRangeMin, $tlRangeMax, 'm') }}</dd>
                </div>
            @endif
            @if ($delArmTime !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Arm Time</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($delArmTime, 's', 1) }}</dd>
                </div>
            @endif
            @if ($clusterSize !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cluster Size</dt>
                    <dd class="text-sm font-medium">{{ fmt_or_dash($clusterSize, 0) }}</dd>
                </div>
            @endif
        </dl>


    {{-- Secondary Data: Target Lock (Collapsible, expanded by default) --}}
    @if ($hasTargetLock)
        <details class="collapse collapse-arrow border border-base-300 bg-base-100">
            <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                Target Lock
            </summary>
            <div class="collapse-content">
                <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">

                    @if ($tlAngle !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lock Angle</dt>
                            <dd class="text-sm font-medium">{{ fmt_value_with_unit($tlAngle, 'deg', 1) }}</dd>
                        </div>
                    @endif
                    @if ($trackingSignalMin !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tracking Signal Min</dt>
                            <dd class="text-sm font-medium">{{ fmt_or_dash($trackingSignalMin, 2) }}</dd>
                        </div>
                    @endif
                    @if ($tlSignalResilienceMin !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Signal Resilience Min</dt>
                            <dd class="text-sm font-medium">{{ fmt_or_dash($tlSignalResilienceMin, 2) }}</dd>
                        </div>
                    @endif
                    @if ($tlSignalResilienceMax !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Signal Resilience Max</dt>
                            <dd class="text-sm font-medium">{{ fmt_or_dash($tlSignalResilienceMax, 2) }}</dd>
                        </div>
                    @endif
                    @if ($tlSignalAmplifier !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Signal Amplifier</dt>
                            <dd class="text-sm font-medium">{{ fmt_or_dash($tlSignalAmplifier, 2) }}</dd>
                        </div>
                    @endif
                    @if ($tlIncreaseRate !== null)
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lock Increase Rate</dt>
                            <dd class="text-sm font-medium">{{ fmt_value_with_unit($tlIncreaseRate, '/s', 2) }}</dd>
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
        </details>
    @endif


    @if ($hasFlight)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Flight Performance
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($flightSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Speed</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightSpeed, 'm', 2) }}/s</dd>
                            </div>
                        @endif
                            @if ($flightMaxLifetime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Lifetime</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightMaxLifetime, 's', 2) }}</dd>
                                </div>
                            @endif
                        @if ($flightBoostSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Boost Speed</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightBoostSpeed, 'm', 2) }}/s</dd>
                            </div>
                        @endif
                        @if ($flightInterceptSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Intercept Speed</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightInterceptSpeed, 'm', 2) }}/s</dd>
                            </div>
                        @endif
                        @if ($flightTerminalSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Terminal Speed</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightTerminalSpeed, 'm', 2) }}/s</dd>
                            </div>
                        @endif
                        @if ($flightFuelTankSize !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Tank Size</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($flightFuelTankSize) }}</dd>
                            </div>
                        @endif
                        @if ($flightBoostPhaseDuration !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Boost Phase Duration</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightBoostPhaseDuration, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($flightTerminalPhaseEngagementTime !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Terminal Phase Engagement Time</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightTerminalPhaseEngagementTime, 's', 2) }}</dd>
                            </div>
                        @endif
                        @if ($flightTerminalPhaseEngagementAngle !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Terminal Phase Engagement Angle</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($flightTerminalPhaseEngagementAngle, 'deg', 1) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        {{-- Tertiary Data (Collapsed by default) --}}
        @if ($hasExplosion || $hasDelays || $hasDamage)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3">
                    <h3 class="text-sm font-semibold">Arming & Explosion</h3>
                </summary>
                <div class="collapse-content">
                    {{-- Damage --}}
                    @if ($hasDamage)
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Damage</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2 mb-4">

                            @if ($hasDamageMap)
                                @foreach ($damageMap as $type => $value)
                                    <div class="space-y-1">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($type) }}</dt>
                                        <dd class="text-sm font-medium">{{ fmt_or_dash($value, 2) }}</dd>
                                    </div>
                                @endforeach
                            @endif
                        </dl>
                    @endif

                    {{-- Explosion --}}
                    @if ($hasExplosion)
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Explosion</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2 mb-4">
                            @if ($expIsCluster !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Is Cluster</dt>
                                    <dd class="text-sm font-medium">{{ $expIsCluster ? 'Yes' : 'No' }}</dd>
                                </div>
                            @endif
                            @if ($expClusterSize !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cluster Size</dt>
                                    <dd class="text-sm font-medium">{{ fmt_or_dash($expClusterSize, 0) }}</dd>
                                </div>
                            @endif
                            @if ($expRequiresLauncher !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Requires Launcher</dt>
                                    <dd class="text-sm font-medium">{{ $expRequiresLauncher ? 'Yes' : 'No' }}</dd>
                                </div>
                            @endif

                            @if ($expRadiusMin !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radius</dt>
                                    <dd class="text-sm font-medium">{{ fmt_range($expRadiusMin, $expRadiusMax, 'm', 2) }}</dd>
                                </div>
                            @endif
                            @if ($expSafetyDistance !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Safety Distance</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($expSafetyDistance, 'm', 2) }}</dd>
                                </div>
                            @endif
                            @if ($expProximity !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Proximity</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($expProximity, 'm', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    {{-- Delays --}}
                    @if ($hasDelays)
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Delays</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">

                            @if ($delArmTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Arm Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($delArmTime, 's', 2) }}</dd>
                                </div>
                            @endif
                            @if ($delIgniteTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ignite Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($delIgniteTime, 's', 2) }}</dd>
                                </div>
                            @endif
                            @if ($delCollisionDelayTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Collision Delay Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($delCollisionDelayTime, 's', 2) }}</dd>
                                </div>
                            @endif
                            @if ($delLockTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Lock Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($delLockTime, 's', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                </div>
            </details>
        @endif
    </div>
</div>
