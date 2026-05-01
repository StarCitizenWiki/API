@props([
    'shieldController',
])

@php
    $faceType = data_get($shieldController, 'face_type');
    $maxReallocation = data_get($shieldController, 'max_reallocation');
    $reconfigurationCooldown = data_get($shieldController, 'reconfiguration_cooldown');
    $maxElectricalChargeDamageRate = data_get($shieldController, 'max_electrical_charge_damage_rate');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Shield Controller</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Face Type</dt>
                <dd class="text-sm font-semibold text-base-content">{{ $faceType ?? '—' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Reconfiguration Cooldown</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($reconfigurationCooldown, 's', 1) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Reallocation</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_or_dash($maxReallocation, 0) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Electrical Charge Dmg</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($maxElectricalChargeDamageRate, '/s', 1) }}</dd>
            </div>
        </dl>
    </div>
</div>
