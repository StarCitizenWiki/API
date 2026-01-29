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
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="engine" class="size-4 text-primary" />
            <span>Thruster</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($role !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Role</dt>
                    <dd class="text-sm font-medium">{{ $role }}</dd>
                </div>
            @endif
            @if ($vtolOnly !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">VTOL Only</dt>
                    <dd class="text-sm font-medium">{{ $vtolOnly ? 'Yes' : 'No' }}</dd>
                </div>
            @endif
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thrust Capacity</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($thrustCapacity, 'N', 0, true) }}</dd>
            </div>
        </dl>

        @if ($maxAtmosphericEfficiency !== null || $minHealthThrustMultiplier !== null || $burnRatePer10kNewton !== null)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Performance
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($maxAtmosphericEfficiency !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Atmospheric Efficiency</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($maxAtmosphericEfficiency, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($minHealthThrustMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Health Thrust Multiplier</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($minHealthThrustMultiplier, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($burnRatePer10kNewton !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Burn Rate per 10k N</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($burnRatePer10kNewton, '', 3) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($backwashEnabled !== null || $backwashAutomateSize !== null || $backwashMaxSpeed !== null || $backwashMaxDensity !== null || $backwashMaxResistance !== null || $backwashAfterburnerMultiplier !== null)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Backwash
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                        @if ($backwashEnabled !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Enabled</dt>
                                <dd class="text-sm font-medium">{{ $backwashEnabled ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($backwashAutomateSize !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Automate Size</dt>
                                <dd class="text-sm font-medium">{{ $backwashAutomateSize ? 'Yes' : 'No' }}</dd>
                            </div>
                        @endif
                        @if ($backwashMaxSpeed !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Speed</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($backwashMaxSpeed, 'm/s', 2) }}</dd>
                            </div>
                        @endif
                        @if ($backwashMaxDensity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Density</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($backwashMaxDensity, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($backwashMaxResistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Resistance</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($backwashMaxResistance, '', 2) }}</dd>
                            </div>
                        @endif
                        @if ($backwashAfterburnerMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Afterburner Multiplier</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($backwashAfterburnerMultiplier, '', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($strengthSmoothing !== null)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Handling
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2">
                        @if ($strengthSmoothing !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Strength Smoothing</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($strengthSmoothing, '', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif
    </div>
</div>
