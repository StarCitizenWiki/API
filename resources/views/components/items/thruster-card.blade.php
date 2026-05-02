@use('App\Support\Format')
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

        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($role !== null)
                <x-dt-dd label="Role">{{ $role }}</x-dt-dd>
            @endif
            @if ($vtolOnly !== null)
                <x-dt-dd label="VTOL Only">{{ $vtolOnly ? 'Yes' : 'No' }}</x-dt-dd>
            @endif
            <x-dt-dd label="Thrust Capacity">{{ Format::valueWithUnit($thrustCapacity, 'N', 0, true) }}</x-dt-dd>
        </x-dl-section>

        @if ($maxAtmosphericEfficiency !== null || $minHealthThrustMultiplier !== null || $burnRatePer10kNewton !== null)
            <x-dl-details title="Performance" :open="true">
                @if ($maxAtmosphericEfficiency !== null)
                    <x-dt-dd label="Max Atmospheric Efficiency">{{ Format::valueWithUnit($maxAtmosphericEfficiency, '', 2) }}</x-dt-dd>
                @endif
                @if ($minHealthThrustMultiplier !== null)
                    <x-dt-dd label="Min Health Thrust Multiplier">{{ Format::valueWithUnit($minHealthThrustMultiplier, '', 2) }}</x-dt-dd>
                @endif
                @if ($burnRatePer10kNewton !== null)
                    <x-dt-dd label="Burn Rate per 10k N">{{ Format::valueWithUnit($burnRatePer10kNewton, '', 3) }}</x-dt-dd>
                @endif
            </x-dl-details>
        @endif

        @if ($backwashEnabled !== null || $backwashAutomateSize !== null || $backwashMaxSpeed !== null || $backwashMaxDensity !== null || $backwashMaxResistance !== null || $backwashAfterburnerMultiplier !== null)
            <x-dl-details title="Backwash">
                @if ($backwashEnabled !== null)
                    <x-dt-dd label="Enabled">{{ $backwashEnabled ? 'Yes' : 'No' }}</x-dt-dd>
                @endif
                @if ($backwashAutomateSize !== null)
                    <x-dt-dd label="Automate Size">{{ $backwashAutomateSize ? 'Yes' : 'No' }}</x-dt-dd>
                @endif
                @if ($backwashMaxSpeed !== null)
                    <x-dt-dd label="Max Speed">{{ Format::valueWithUnit($backwashMaxSpeed, 'm/s', 2) }}</x-dt-dd>
                @endif
                @if ($backwashMaxDensity !== null)
                    <x-dt-dd label="Max Density">{{ Format::valueWithUnit($backwashMaxDensity, '', 2) }}</x-dt-dd>
                @endif
                @if ($backwashMaxResistance !== null)
                    <x-dt-dd label="Max Resistance">{{ Format::valueWithUnit($backwashMaxResistance, '', 2) }}</x-dt-dd>
                @endif
                @if ($backwashAfterburnerMultiplier !== null)
                    <x-dt-dd label="Afterburner Multiplier">{{ Format::valueWithUnit($backwashAfterburnerMultiplier, '', 2) }}</x-dt-dd>
                @endif
            </x-dl-details>
        @endif

        @if ($strengthSmoothing !== null)
            <x-dl-details title="Handling">
                @if ($strengthSmoothing !== null)
                    <x-dt-dd label="Strength Smoothing">{{ Format::valueWithUnit($strengthSmoothing, '', 2) }}</x-dt-dd>
                @endif
            </x-dl-details>
        @endif
    </div>
</div>
