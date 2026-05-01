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
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Self Destruct</h2>
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Damage</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($damage, '', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Countdown</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($countdown, 's', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Radius</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_range($minRadius, $radius, 'm', 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Physical Impact Radius</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_range($minPhysRadius, $physRadius, 'm', 0) }}</dd>
            </div>
        </dl>
    </div>
</div>
