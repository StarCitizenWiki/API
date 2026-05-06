@use('App\Support\Format')
@props([
    'suitArmor',
    'temperatureResistance',
    'inventory',
])

@php
    $slot = data_get($suitArmor, 'slot');

    $damageResistanceMap = data_get($suitArmor, 'damage_resistance_map', []);
    $signature = data_get($suitArmor, 'signature', []);
    $radiationResistance = data_get($suitArmor, 'radiation_resistance', []);
    $temperatureResistance = $temperatureResistance ?? [];
    $scuConverted = data_get($inventory, 'scu_converted');
    $inventoryUnit = data_get($inventory, 'unit', 'SCU');

    // Damage change metrics — 6 types, rendered in Damage Resistance section
    $damageTypes = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun'];

    $damageChangeMetrics = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => Str::headline($type),
            'value' => data_get($damageResistanceMap, $type . '_change'),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null,
    ));

    // Signature metrics — dynamic key-value pairs
    $signatureMetrics = collect($signature)
        ->filter(static fn ($value): bool => $value !== null)
        ->map(static fn ($value, $key): array => ['label' => $key, 'value' => $value])
        ->values()
        ->all();

    // Radiation resistance metrics
    $radiationResistanceMetrics = [
        ['label' => 'Max Radiation Capacity', 'value' => data_get($radiationResistance, 'maximum_radiation_capacity')],
        ['label' => 'Dissipation Rate', 'value' => data_get($radiationResistance, 'radiation_dissipation_rate')],
    ];


@endphp

<x-item-card title="Suit Armor">

    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Slot" :value="$slot">{{ $slot }}</x-dt-dd>
            <x-dt-dd label="Inventory" :value="$scuConverted">{{ Format::valueWithUnit($scuConverted, $inventoryUnit, 1) }}</x-dt-dd>
        </x-slot:head>
        <x-dl-section title="Damage Resistance">
            @foreach ($damageChangeMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    <span class="{{ Format::colorClass($metric['value']) }}">{{ Format::valueWithUnit($metric['value'] * 100, '%', 1) }}</span>
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Signature">
            @foreach ($signatureMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], '', 2) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Temperature Resistance">
            <x-dt-dd label="Min" :value="data_get($temperatureResistance, 'minimum')">{{ Format::valueWithUnit(data_get($temperatureResistance, 'minimum'), '°C', 1) }}</x-dt-dd>
            <x-dt-dd label="Max" :value="data_get($temperatureResistance, 'maximum')">{{ Format::valueWithUnit(data_get($temperatureResistance, 'maximum'), '°C', 1) }}</x-dt-dd>
        </x-dl-section>

        <x-dl-section title="Radiation Resistance">
            @foreach ($radiationResistanceMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], '', 2) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
