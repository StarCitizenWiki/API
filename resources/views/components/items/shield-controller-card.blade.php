@props([
    'shieldController',
])

@php
    $faceType = data_get($shieldController, 'face_type');
    $maxReallocation = data_get($shieldController, 'max_reallocation');
    $reconfigurationCooldown = data_get($shieldController, 'reconfiguration_cooldown');
    $maxElectricalChargeDamageRate = data_get($shieldController, 'max_electrical_charge_damage_rate');
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="shield-user" class="size-4 text-primary" />
            <span>Shield Controller Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($faceType !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Face Type</dt>
                    <dd class="text-sm font-medium">{{ $faceType }}</dd>
                </div>
            @endif
            @if ($maxReallocation !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Reallocation</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$maxReallocation, 2) }}</dd>
                </div>
            @endif
            @if ($reconfigurationCooldown !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Reconfiguration Cooldown</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$reconfigurationCooldown, 2) }}s</dd>
                </div>
            @endif
            @if ($maxElectricalChargeDamageRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Electrical Charge Damage Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$maxElectricalChargeDamageRate, 2) }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
