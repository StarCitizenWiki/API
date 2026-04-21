@props([
    'thruster',
])

@php
    $role = data_get($thruster, 'role');
    $vtolOnly = data_get($thruster, 'vtol_only');

    $performance = data_get($thruster, 'performance', []);
    $thrustCapacity = data_get($performance, 'thrust_capacity');
    $thrustCapacityNew = data_get($performance, 'thrust_capacity_new');
    $maxAtmosphericEfficiency = data_get($performance, 'max_supported_atmospheric_efficiency');
    $minHealthThrustMultiplier = data_get($performance, 'min_health_thrust_multiplier');

    $fuel = data_get($thruster, 'fuel', []);
    $burnRatePer10kNewton = data_get($fuel, 'burn_rate_per_10k_newton');

    $backwash = data_get($thruster, 'backwash', []);
    $backwashEnabled = data_get($backwash, 'enabled');
    $backwashAutomateSize = data_get($backwash, 'automate_size');
    $backwashMaxSpeed = data_get($backwash, 'max_speed');
    $backwashMaxDensity = data_get($backwash, 'max_density');
    $backwashMaxResistance = data_get($backwash, 'max_resistance');
    $backwashAfterburnerMultiplier = data_get($backwash, 'afterburner_multiplier');

    $handling = data_get($thruster, 'handling', []);
    $strengthSmoothing = data_get($handling, 'strength_smoothing');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Thruster</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($role !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Role</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ $role }}</dd>
                </div>
            @endif
            @if ($vtolOnly !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">VTOL Only</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ $vtolOnly ? 'Yes' : 'No' }}</dd>
                </div>
            @endif
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Thrust Capacity</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($thrustCapacity, 'N', 0, true) }}</dd>
            </div>
        </dl>

        @if ($maxAtmosphericEfficiency !== null || $minHealthThrustMultiplier !== null || $burnRatePer10kNewton !== null)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Performance
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($maxAtmosphericEfficiency !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Atmospheric Efficiency</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($maxAtmosphericEfficiency, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($minHealthThrustMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Min Health Thrust Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($minHealthThrustMultiplier, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($burnRatePer10kNewton !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Burn Rate per 10k N</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($burnRatePer10kNewton, '', 3) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($backwashEnabled !== null || $backwashAutomateSize !== null || $backwashMaxSpeed !== null || $backwashMaxDensity !== null || $backwashMaxResistance !== null || $backwashAfterburnerMultiplier !== null)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Backwash
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($backwashEnabled !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Enabled</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $backwashEnabled ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($backwashAutomateSize !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Automate Size</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $backwashAutomateSize ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($backwashMaxSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Speed</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($backwashMaxSpeed, 'm/s', 2) }}</dd>
                            </div>
                        @endif
                        @if ($backwashMaxDensity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Density</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($backwashMaxDensity, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($backwashMaxResistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Max Resistance</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($backwashMaxResistance, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($backwashAfterburnerMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Afterburner Multiplier</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($backwashAfterburnerMultiplier, '', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($strengthSmoothing !== null)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Handling
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($strengthSmoothing !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Strength Smoothing</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($strengthSmoothing, '', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif
    </div>
</div>
