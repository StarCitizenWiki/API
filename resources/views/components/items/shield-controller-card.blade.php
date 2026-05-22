@use('App\Support\Format')
@props([
    'shieldController',
])

@php
    $faceType = data_get($shieldController, 'face_type');
    $reconfigurationCooldown = data_get($shieldController, 'reconfiguration_cooldown');
    $maxReallocation = data_get($shieldController, 'max_reallocation');
    $electricalChargeDmg = data_get($shieldController, 'max_electrical_charge_damage_rate');

    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'Face Type', 'value' => $faceType],
                ['label' => 'Reconfiguration Cooldown', 'value' => Format::valueWithUnit($reconfigurationCooldown, 's', 1)],
                ['label' => 'Max Reallocation', 'value' => Format::numberOrDash($maxReallocation, 0)],
                ['label' => 'Electrical Charge Dmg', 'value' => Format::valueWithUnit($electricalChargeDmg, '/s', 1)],
            ], static fn (array $row): bool => $row['value'] !== '-' && $row['value'] !== null)),
        ],
    ];
@endphp

<x-data-card title="Shield Controller" :sections="$sections" />
