@use('App\Support\Format')
@props([
    'emp',
])

@php
    $chargeDuration = data_get($emp, 'charge_duration');
    $unleashDuration = data_get($emp, 'unleash_duration');
    $cooldownDuration = data_get($emp, 'cooldown_duration');
    $distortionDamage = data_get($emp, 'distortion_damage');

@endphp

<x-item-card title="EMP Generator">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="EMP Radius" :value="data_get($emp, 'min_emp_radius') ?? data_get($emp, 'emp_radius')">{{ Format::range(data_get($emp, 'min_emp_radius'), data_get($emp, 'emp_radius'), 'm', 2) }}</x-dt-dd>
            <x-dt-dd label="Charge Duration" :value="$chargeDuration">{{ Format::valueWithUnit($chargeDuration, 's', 2) }}</x-dt-dd>
            <x-dt-dd label="Unleash Duration" :value="$unleashDuration">{{ Format::valueWithUnit($unleashDuration, 's', 2) }}</x-dt-dd>
            <x-dt-dd label="Cooldown Duration" :value="$cooldownDuration">{{ Format::valueWithUnit($cooldownDuration, 's', 2) }}</x-dt-dd>
            <x-dt-dd label="Distortion Damage" :value="$distortionDamage">{{ Format::valueWithUnit($distortionDamage, 'N', 2) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
