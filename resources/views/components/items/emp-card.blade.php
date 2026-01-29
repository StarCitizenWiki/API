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

    $distortionDamageFormatted = fmt_value_with_unit($distortionDamage, 'N', 2);
    $empRadiusFormatted = fmt_range($minEmpRadius, $empRadius, 'm', 2);
    $chargeDurationFormatted = fmt_value_with_unit($chargeDuration, 's', 2);
    $unleashDurationFormatted = fmt_value_with_unit($unleashDuration, 's', 2);
    $cooldownDurationFormatted = fmt_value_with_unit($cooldownDuration, 's', 2);
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title flex items-center gap-2">
            <x-icon name="zap" class="size-4 text-primary" />
            <span>EMP Generator</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EMP Radius</dt>
                <dd class="text-sm font-medium">{{ $empRadiusFormatted }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Charge Duration</dt>
                <dd class="text-sm font-medium">{{ $chargeDurationFormatted }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Unleash Duration</dt>
                <dd class="text-sm font-medium">{{ $unleashDurationFormatted }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Duration</dt>
                <dd class="text-sm font-medium">{{ $cooldownDurationFormatted }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Distortion Damage</dt>
                <dd class="text-sm font-medium">{{ $distortionDamageFormatted }}</dd>
            </div>
        </dl>
    </div>
</div>
