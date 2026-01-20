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
    $hasPerformance = is_array($performance) && array_filter($performance, fn($v) => $v !== null);

    $fuel = data_get($thruster, 'fuel', []);
    $burnRatePer10kNewton = data_get($fuel, 'burn_rate_per_10k_newton');
    $hasFuel = is_array($fuel) && array_filter($fuel, fn($v) => $v !== null);

    $backwash = data_get($thruster, 'backwash', []);
    $backwashEnabled = data_get($backwash, 'enabled');
    $backwashAutomateSize = data_get($backwash, 'automate_size');
    $backwashMaxSpeed = data_get($backwash, 'max_speed');
    $backwashMaxDensity = data_get($backwash, 'max_density');
    $backwashMaxResistance = data_get($backwash, 'max_resistance');
    $backwashAfterburnerMultiplier = data_get($backwash, 'afterburner_multiplier');
    $hasBackwash = is_array($backwash) && array_filter($backwash, fn($v) => $v !== null);

    $handling = data_get($thruster, 'handling', []);
    $strengthSmoothing = data_get($handling, 'strength_smoothing');
    $hasHandling = is_array($handling) && array_filter($handling, fn($v) => $v !== null);
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="arrow-up" class="size-4 text-primary" />
            <span>Thruster Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
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
        </dl>

        @if ($hasPerformance)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Performance</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($thrustCapacity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thrust Capacity</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$thrustCapacity, 2) }} N</dd>
                            </div>
                        @endif
                        @if ($thrustCapacityNew !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Thrust Capacity (New)</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$thrustCapacityNew, 2) }} N</dd>
                            </div>
                        @endif
                        @if ($maxAtmosphericEfficiency !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Atmospheric Efficiency</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$maxAtmosphericEfficiency, 2) }}</dd>
                            </div>
                        @endif
                        @if ($minHealthThrustMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Min Health Thrust Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$minHealthThrustMultiplier, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasFuel)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Fuel</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($burnRatePer10kNewton !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Burn Rate per 10k Newton</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$burnRatePer10kNewton, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasBackwash)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Backwash</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
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
                                <dd class="text-sm font-medium">{{ number_format((float)$backwashMaxSpeed, 2) }} m/s</dd>
                            </div>
                        @endif
                        @if ($backwashMaxDensity !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Density</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$backwashMaxDensity, 2) }}</dd>
                            </div>
                        @endif
                        @if ($backwashMaxResistance !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Resistance</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$backwashMaxResistance, 2) }}</dd>
                            </div>
                        @endif
                        @if ($backwashAfterburnerMultiplier !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Afterburner Multiplier</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$backwashAfterburnerMultiplier, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasHandling)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Handling</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($strengthSmoothing !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Strength Smoothing</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$strengthSmoothing, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
