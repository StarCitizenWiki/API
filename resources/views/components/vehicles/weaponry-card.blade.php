@use('App\Support\Format')
@props(['vehicle'])

@php
    $weaponry = data_get($vehicle, 'weaponry', []);

    $pilotDps = data_get($weaponry, 'pilot_dps');
    $pilotAlpha = data_get($weaponry, 'pilot_alpha');
    $pilotSustainedDps = data_get($weaponry, 'pilot_sustained_dps');

    $turretDps = data_get($weaponry, 'turret_dps');
    $turretAlpha = data_get($weaponry, 'turret_alpha');
    $turretSustainedDps = data_get($weaponry, 'turret_sustained_dps');

    $missileCount = data_get($weaponry, 'missiles.count');
    $totalMissileDamage = data_get($weaponry, 'total_missile_damage');

    $sections = [];

    $pilotRows = array_values(array_filter([
        ['label' => 'DPS', 'value' => $pilotDps !== null ? Format::numberOrDash($pilotDps, 1) . ' DPS' : null],
        ['label' => 'Sustained DPS', 'value' => $pilotSustainedDps !== null ? Format::numberOrDash($pilotSustainedDps, 1) . ' DPS' : null],
        ['label' => 'Alpha', 'value' => $pilotAlpha !== null ? Format::numberOrDash($pilotAlpha, 1) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($pilotRows !== []) {
        $sections[] = ['title' => 'Pilot Weapons', 'rows' => $pilotRows];
    }

    $turretRows = array_values(array_filter([
        ['label' => 'DPS', 'value' => $turretDps !== null ? Format::numberOrDash($turretDps, 1) . ' DPS' : null],
        ['label' => 'Sustained DPS', 'value' => $turretSustainedDps !== null ? Format::numberOrDash($turretSustainedDps, 1) . ' DPS' : null],
        ['label' => 'Alpha', 'value' => $turretAlpha !== null ? Format::numberOrDash($turretAlpha, 1) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($turretRows !== []) {
        $sections[] = ['title' => 'Turrets', 'rows' => $turretRows];
    }

    $missileRows = array_values(array_filter([
        ['label' => 'Count', 'value' => $missileCount !== null ? Format::numberOrDash($missileCount) : null],
        ['label' => 'Total Damage', 'value' => $totalMissileDamage !== null ? Format::numberOrDash($totalMissileDamage) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($missileRows !== []) {
        $sections[] = ['title' => 'Missiles', 'rows' => $missileRows];
    }
@endphp

<x-data-card title="Weaponry" :sections="$sections" {{ $attributes }} />
