@props([
    'selfDestruct',
])

@php
    $damage = data_get($selfDestruct, 'damage');
    $radius = data_get($selfDestruct, 'radius');
    $minRadius = data_get($selfDestruct, 'min_radius');
    $physRadius = data_get($selfDestruct, 'phys_radius');
    $minPhysRadius = data_get($selfDestruct, 'min_phys_radius');
    $countdown = data_get($selfDestruct, 'countdown');

    $hasDamage = $damage !== null;
    $hasRadius = $radius !== null;
    $hasMinRadius = $minRadius !== null;
    $hasPhysRadius = $physRadius !== null;
    $hasMinPhysRadius = $minPhysRadius !== null;
    $hasCountdown = $countdown !== null;
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="octagon-alert" class="size-4 text-primary" />
            <span>Self Destruct Specifications</span>
        </h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($hasDamage)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage</dt>
                    <dd class="text-sm font-medium">{{ (int)$damage }}</dd>
                </div>
            @endif
            @if ($hasCountdown)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Countdown</dt>
                    <dd class="text-sm font-medium">{{ (int)$countdown }}s</dd>
                </div>
            @endif
            @if ($hasRadius)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Maximum Radius</dt>
                    <dd class="text-sm font-medium">{{ (int)$radius }}m</dd>
                </div>
            @endif
            @if ($hasMinRadius)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum Radius</dt>
                    <dd class="text-sm font-medium">{{ (int)$minRadius }}m</dd>
                </div>
            @endif
            @if ($hasPhysRadius)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Physical Impact Radius</dt>
                    <dd class="text-sm font-medium">{{ (int)$physRadius }}m</dd>
                </div>
            @endif
            @if ($hasMinPhysRadius)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Physical Impact Radius</dt>
                    <dd class="text-sm font-medium">{{ (int)$minPhysRadius }}m</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
