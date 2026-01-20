@props([
    'bomb',
])

@php
    $armTime = data_get($bomb, 'arm_time');
    $igniteTime = data_get($bomb, 'ignite_time');
    $collisionDelayTime = data_get($bomb, 'collision_delay_time');
    $maximumDropAngle = data_get($bomb, 'maximum_drop_angle');
    $damageTotal = data_get($bomb, 'damage_total');

    $explosion = data_get($bomb, 'explosion', []);
    $expRequiresLauncher = data_get($explosion, 'requires_launcher');
    $expRadiusMin = data_get($explosion, 'radius_min');
    $expRadiusMax = data_get($explosion, 'radius_max');
    $expSafetyDistance = data_get($explosion, 'safety_distance');
    $expProximity = data_get($explosion, 'proximity');

    $hasExplosion = is_array($explosion) && array_filter($explosion, fn($v) => $v !== null);

    $damageMap = data_get($bomb, 'damage_map', []);
    $hasDamageMap = is_array($damageMap) && $damageMap !== [];
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="bomb" class="size-4 text-primary" />
            <span>Bomb Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($armTime !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Arm Time</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$armTime, 2) }} s</dd>
                </div>
            @endif
            @if ($igniteTime !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ignite Time</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$igniteTime, 2) }} s</dd>
                </div>
            @endif
            @if ($collisionDelayTime !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Collision Delay Time</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$collisionDelayTime, 2) }} s</dd>
                </div>
            @endif
            @if ($maximumDropAngle !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Drop Angle</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$maximumDropAngle, 2) }} deg</dd>
                </div>
            @endif
            @if ($damageTotal !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage Total</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$damageTotal, 2) }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasExplosion)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Explosion</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($expRequiresLauncher !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Requires Launcher</dt>
                                <dd class="text-sm font-medium">{{ $expRequiresLauncher ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($expRadiusMin !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Radius</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$expRadiusMin, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($expRadiusMax !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Radius</dt>
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

        @if ($hasDamageMap)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Damage Map</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @foreach ($damageMap as $type => $value)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($type) }}</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$value, 2) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
