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

<x-item-card title="Thruster">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Role" :value="$role">{{ $role }}</x-dt-dd>
            <x-dt-dd label="VTOL Only" :value="$vtolOnly">{{ $vtolOnly ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="Thrust Capacity" :value="$thrustCapacity">{{ Format::valueWithUnit($thrustCapacity, 'N', 0, true) }}</x-dt-dd>
        </x-slot:head>

        <x-dl-details title="Performance" :open="true">
            <x-dt-dd label="Max Atmospheric Efficiency" :value="$maxAtmosphericEfficiency">{{ Format::valueWithUnit($maxAtmosphericEfficiency, '', 2) }}</x-dt-dd>
            <x-dt-dd label="Min Health Thrust Multiplier" :value="$minHealthThrustMultiplier">{{ Format::valueWithUnit($minHealthThrustMultiplier, '', 2) }}</x-dt-dd>
            <x-dt-dd label="Burn Rate per 10k N" :value="$burnRatePer10kNewton">{{ Format::valueWithUnit($burnRatePer10kNewton, '', 3) }}</x-dt-dd>
        </x-dl-details>

        <x-dl-details title="Backwash">
            <x-dt-dd label="Enabled" :value="$backwashEnabled">{{ $backwashEnabled ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="Automate Size" :value="$backwashAutomateSize">{{ $backwashAutomateSize ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="Max Speed" :value="$backwashMaxSpeed">{{ Format::valueWithUnit($backwashMaxSpeed, 'm/s', 2) }}</x-dt-dd>
            <x-dt-dd label="Max Density" :value="$backwashMaxDensity">{{ Format::valueWithUnit($backwashMaxDensity, '', 2) }}</x-dt-dd>
            <x-dt-dd label="Max Resistance" :value="$backwashMaxResistance">{{ Format::valueWithUnit($backwashMaxResistance, '', 2) }}</x-dt-dd>
            <x-dt-dd label="Afterburner Multiplier" :value="$backwashAfterburnerMultiplier">{{ Format::valueWithUnit($backwashAfterburnerMultiplier, '', 2) }}</x-dt-dd>
        </x-dl-details>

        <x-dl-details title="Handling">
            <x-dt-dd label="Strength Smoothing" :value="$strengthSmoothing">{{ Format::valueWithUnit($strengthSmoothing, '', 2) }}</x-dt-dd>
        </x-dl-details>
    </x-dl-container>
</x-item-card>
