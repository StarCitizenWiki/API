@use('App\Support\Format')
@props([
    'shieldController',
])

@php
    $faceType = data_get($shieldController, 'face_type');
    $reconfigurationCooldown = data_get($shieldController, 'reconfiguration_cooldown');
    $maxReallocation = data_get($shieldController, 'max_reallocation');
    $electricalChargeDmg = data_get($shieldController, 'max_electrical_charge_damage_rate');

@endphp

<x-item-card title="Shield Controller">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Face Type" :value="$faceType">{{ $faceType }}</x-dt-dd>
            <x-dt-dd label="Reconfiguration Cooldown" :value="$reconfigurationCooldown">{{ Format::valueWithUnit($reconfigurationCooldown, 's', 1) }}</x-dt-dd>
            <x-dt-dd label="Max Reallocation" :value="$maxReallocation">{{ Format::numberOrDash($maxReallocation, 0) }}</x-dt-dd>
            <x-dt-dd label="Electrical Charge Dmg" :value="$electricalChargeDmg">{{ Format::valueWithUnit($electricalChargeDmg, '/s', 1) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
