@use('App\Support\Format')
@props(['vehicle'])

@php
    $armor = data_get($vehicle, 'armor', []);
    $health = data_get($armor, 'health');

    $deflectionPhysical = data_get($armor, 'deflection.physical');
    $deflectionEnergy = data_get($armor, 'deflection.energy');

    $resistanceMultipliers = data_get($armor, 'resistance_multipliers', []);
    $signalMultipliers = data_get($armor, 'signal_multipliers', []);

    $sections = [];

    $hDefRows = array_values(array_filter([
        ['label' => 'Health', 'value' => $health !== null ? Format::numberOrDash($health) . ' HP' : null],
        ['label' => 'Physical Def.', 'value' => $deflectionPhysical !== null ? Format::numberOrDash($deflectionPhysical) : null],
        ['label' => 'Energy Def.', 'value' => $deflectionEnergy !== null ? Format::numberOrDash($deflectionEnergy) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($hDefRows !== []) {
        $sections[] = ['title' => 'Health & Deflection', 'rows' => $hDefRows];
    }

    $damageRows = array_values(array_filter([
        ['label' => 'Physical', 'value' => data_get($resistanceMultipliers, 'physical')],
        ['label' => 'Energy', 'value' => data_get($resistanceMultipliers, 'energy')],
        ['label' => 'Distortion', 'value' => data_get($resistanceMultipliers, 'distortion')],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($damageRows !== []) {
        $sections[] = ['title' => 'Damage Multipliers', 'rows' => array_map(static fn (array $row): array => [
            'label' => $row['label'],
            'value' => Format::signedPercent($row['value']),
            'class' => Format::colorClass($row['value'] - 1),
        ], $damageRows)];
    }

    $signalRows = array_values(array_filter([
        ['label' => 'EM', 'value' => data_get($signalMultipliers, 'electromagnetic')],
        ['label' => 'IR', 'value' => data_get($signalMultipliers, 'infrared')],
        ['label' => 'CS', 'value' => data_get($signalMultipliers, 'cross_section')],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($signalRows !== []) {
        $sections[] = ['title' => 'Signal Multipliers', 'rows' => array_map(static fn (array $row): array => [
            'label' => $row['label'],
            'value' => Format::signedPercent($row['value']),
            'class' => Format::colorClass($row['value'] - 1),
        ], $signalRows)];
    }
@endphp

<x-data-card title="Armor" :sections="$sections" {{ $attributes }} />
