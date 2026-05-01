@props([
    'bomb',
])

@php
    // Primary: damage_total
    $damageTotal = data_get($bomb, 'damage_total');

    // Secondary: explosion parameters
    $explosion = data_get($bomb, 'explosion', []);
    $expRequiresLauncher = data_get($explosion, 'requires_launcher');
    $expRadiusMin = data_get($explosion, 'radius_min');
    $expRadiusMax = data_get($explosion, 'radius_max');
    $expSafetyDistance = data_get($explosion, 'safety_distance');
    $expProximity = data_get($explosion, 'proximity');

    $armTime = data_get($bomb, 'arm_time');
    $igniteTime = data_get($bomb, 'ignite_time');
    $collisionDelayTime = data_get($bomb, 'collision_delay_time');
    $maximumDropAngle = data_get($bomb, 'maximum_drop_angle');

    $damageMap = data_get($bomb, 'damage_map', []);

    $hasExplosionData = $expRequiresLauncher !== null || $expRadiusMin !== null || $expRadiusMax !== null || $expSafetyDistance !== null || $expProximity !== null;
    $hasTimingData = $armTime !== null || $igniteTime !== null || $collisionDelayTime !== null || $maximumDropAngle !== null;
    $hasSecondary = $hasExplosionData || $hasTimingData;

    $hasTertiary = is_array($damageMap) && $damageMap !== [];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Bomb</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Damage Total</dt>
                <dd class="text-sm font-semibold text-base-content text-info">{{ fmt_or_dash($damageTotal, 0) }}</dd>
            </div>
        </dl>

        @if ($hasSecondary)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Arming & Explosion
                </summary>
                    <!-- Timing Parameters -->
                    @if ($hasTimingData)
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-2">Timing</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 mb-4 pt-1 pb-2">
                            @if ($armTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Arm Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($armTime, 's', 1) }}</dd>
                                </div>
                            @endif
                            @if ($igniteTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Ignite Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($igniteTime, 's', 1) }}</dd>
                                </div>
                            @endif
                            @if ($collisionDelayTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Collision Delay Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($collisionDelayTime, 's', 1) }}</dd>
                                </div>
                            @endif
                            @if ($maximumDropAngle !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Maximum Drop Angle</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($maximumDropAngle, 'deg', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    <!-- Explosion Parameters -->
                    @if ($hasExplosionData)
                        <h4 class="text-xs font-medium uppercase tracking-wide text-muted mb-2">Explosion</h4>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if ($expRequiresLauncher !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Requires Launcher</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ $expRequiresLauncher ? 'Yes' : 'No' }}</dd>
                                </div>
                            @endif
                            @if ($expRadiusMin !== null || $expRadiusMax !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Radius</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_range($expRadiusMin, $expRadiusMax, 'm', 2) }}</dd>
                                </div>
                            @endif
                            @if ($expSafetyDistance !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Safety Distance</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($expSafetyDistance, 'm', 2) }}</dd>
                                </div>
                            @endif
                            @if ($expProximity !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Proximity</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($expProximity, 'm', 2) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
            </details>
        @endif

        @if ($hasTertiary)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Damage Breakdown
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @foreach ($damageMap as $type => $value)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">{{ \Illuminate\Support\Str::headline($type) }}</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($value, 0) }}</dd>
                            </div>
                        @endforeach
                    </dl>
            </details>
        @endif
    </div>
</div>
