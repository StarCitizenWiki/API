@props([
    'emp',
])

@php
    $distortionDamage = data_get($emp, 'distortion_damage');
    $empRadius = data_get($emp, 'emp_radius');
    $minEmpRadius = data_get($emp, 'min_emp_radius');
    $chargeDuration = data_get($emp, 'charge_duration');
    $unleashDuration = data_get($emp, 'unleash_duration');
    $cooldownDuration = data_get($emp, 'cooldown_duration');
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="zap" class="size-4 text-primary" />
            <span>EMP Generator Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($distortionDamage !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion Damage</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$distortionDamage, 2) }}</dd>
                </div>
            @endif
            @if ($empRadius !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EMP Radius</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$empRadius, 2) }} m</dd>
                </div>
            @endif
            @if ($minEmpRadius !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Minimum EMP Radius</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$minEmpRadius, 2) }} m</dd>
                </div>
            @endif
            @if ($chargeDuration !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Charge Duration</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$chargeDuration, 2) }} s</dd>
                </div>
            @endif
            @if ($unleashDuration !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Unleash Duration</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$unleashDuration, 2) }} s</dd>
                </div>
            @endif
            @if ($cooldownDuration !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Duration</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$cooldownDuration, 2) }} s</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
