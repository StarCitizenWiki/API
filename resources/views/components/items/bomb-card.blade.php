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
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="bomb" class="size-4 text-primary" />
            <span>Bomb</span>
        </h2>

        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage Total</dt>
                <dd class="text-sm font-medium text-info">{{ fmt_or_dash($damageTotal, 0) }}</dd>
            </div>
        </dl>

        @if ($hasSecondary)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Arming & Explosion
                </summary>
                <div class="collapse-content">
                    <!-- Timing Parameters -->
                    @if ($hasTimingData)
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Timing</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2 mb-4">
                            @if ($armTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Arm Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($armTime, 's', 1) }}</dd>
                                </div>
                            @endif
                            @if ($igniteTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ignite Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($igniteTime, 's', 1) }}</dd>
                                </div>
                            @endif
                            @if ($collisionDelayTime !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Collision Delay Time</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($collisionDelayTime, 's', 1) }}</dd>
                                </div>
                            @endif
                            @if ($maximumDropAngle !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Drop Angle</dt>
                                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($maximumDropAngle, 'deg', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    <!-- Explosion Parameters -->
                    @if ($hasExplosionData)
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">Explosion</h4>
                        <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                            @if ($expRequiresLauncher !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Requires Launcher</dt>
                                    <dd class="text-sm font-medium">{{ $expRequiresLauncher ? 'Yes' : 'No' }}</dd>
                                </div>
                            @endif
                            @if ($expRadiusMin !== null || $expRadiusMax !== null)
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
                </div>
            </details>
        @endif

        @if ($hasTertiary)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Damage Breakdown
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">
                        @foreach ($damageMap as $type => $value)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($type) }}</dt>
                                <dd class="text-sm font-medium">{{ fmt_or_dash($value, 0) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </details>
        @endif
    </div>
</div>
