@use('App\Support\Format')
@php use Illuminate\Support\Str; @endphp
@props([
    'suitArmor',
    'temperatureResistance',
    'inventory',
    'gforceResistance',
])

@php
    $slot = data_get($suitArmor, 'slot');

    $damageResistanceMap = data_get($suitArmor, 'damage_resistance_map', []);
    $signature = data_get($suitArmor, 'signature', []);
    $radiationResistance = data_get($suitArmor, 'radiation_resistance', []);
    $temperatureResistance = $temperatureResistance ?? [];
    $scuConverted = data_get($inventory, 'scu_converted');
    $inventoryUnit = data_get($inventory, 'unit', 'SCU');
    $gforceResistance = $gforceResistance ?? null;

    $damageTypes = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun'];

    $damageChangeRows = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => Str::headline($type),
            'value' => data_get($damageResistanceMap, $type . '_change'),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null,
    ));

    $signatureRows = collect($signature)
        ->filter(static fn ($value): bool => $value !== null)
        ->map(static fn ($value, $key): array => ['label' => Str::headline($key), 'value' => Format::valueWithUnit($value, '', 2)])
        ->values()
        ->all();

    $tempRows = array_values(array_filter([
        ['label' => 'Min', 'value' => data_get($temperatureResistance, 'minimum') !== null ? Format::valueWithUnit(data_get($temperatureResistance, 'minimum'), '°C', 1) : null],
        ['label' => 'Max', 'value' => data_get($temperatureResistance, 'maximum') !== null ? Format::valueWithUnit(data_get($temperatureResistance, 'maximum'), '°C', 1) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    $radiationRows = array_values(array_filter([
        ['label' => 'Max Radiation Capacity', 'value' => data_get($radiationResistance, 'maximum_radiation_capacity') !== null ? Format::valueWithUnit(data_get($radiationResistance, 'maximum_radiation_capacity'), '', 2) : null],
        ['label' => 'Dissipation Rate', 'value' => data_get($radiationResistance, 'radiation_dissipation_rate') !== null ? Format::valueWithUnit(data_get($radiationResistance, 'radiation_dissipation_rate'), '', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    $sections = [];

    $infoRows = array_values(array_filter([
        ['label' => 'Slot', 'value' => $slot],
        $scuConverted !== null ? ['label' => 'Inventory', 'value' => Format::valueWithUnit($scuConverted, $inventoryUnit, 1)] : null,
    ], static fn (?array $row): bool => $row !== null && $row['value'] !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    $damageRows = collect($damageChangeRows)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'] * 100, '%', 1), 'class' => Format::colorClass($m['value'])])->all();
    if ($damageRows !== []) {
        $sections[] = ['title' => 'Damage Resistance', 'rows' => $damageRows];
    }

    if ($signatureRows !== []) {
        $sections[] = ['title' => 'Signature', 'rows' => $signatureRows];
    }

    if ($tempRows !== []) {
        $sections[] = ['title' => 'Temperature Resistance', 'rows' => $tempRows];
    }

    if ($gforceResistance !== null) {
        $sections[] = ['title' => 'G-Force Resistance', 'rows' => [['label' => 'Modifier', 'value' => Format::valueWithUnit($gforceResistance * 100, '%', 1), 'class' => Format::colorClass($gforceResistance, true)]]];
    }

    if ($radiationRows !== []) {
        $sections[] = ['title' => 'Radiation Resistance', 'rows' => $radiationRows];
    }
@endphp

<x-data-card title="Suit Armor" :sections="$sections" />
