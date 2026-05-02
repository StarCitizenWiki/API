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
    $radiationResistanceMetrics = array_values(array_filter([
        ['label' => 'Max Radiation Capacity', 'value' => data_get($radiationResistance, 'maximum_radiation_capacity')],
        ['label' => 'Dissipation Rate', 'value' => data_get($radiationResistance, 'radiation_dissipation_rate')],
    ], static fn (array $m): bool => $m['value'] !== null));

    $hasTemperatureResistance = data_get($temperatureResistance, 'minimum') !== null || data_get($temperatureResistance, 'maximum') !== null;
    $hasSections = $damageChangeMetrics !== [] || $signatureMetrics !== [] || $radiationResistanceMetrics !== [] || $hasTemperatureResistance;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Suit Armor</h2>

        <x-dl-container>
            <x-slot:head>
                @if ($slot !== null)
                    <x-dt-dd label="Slot">{{ $slot }}</x-dt-dd>
                @endif
                @if ($scuConverted !== null)
                    <x-dt-dd label="Capacity">{{ Format::valueWithUnit($scuConverted, $inventoryUnit, 1) }}</x-dt-dd>
                @endif
            </x-slot:head>
            @if ($damageChangeMetrics !== [])
                <x-dl-section title="Damage Resistance">
                    @foreach ($damageChangeMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            <span class="{{ Format::colorClass($metric['value']) }}">{{ Format::valueWithUnit($metric['value'] * 100, '%', 1) }}</span>
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif

            @if ($signatureMetrics !== [])
                <x-dl-section title="Signature">
                    @foreach ($signatureMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            {{ Format::valueWithUnit($metric['value'], '', 2) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif

            @if ($hasTemperatureResistance)
                <x-dl-section title="Temperature Resistance">
                    @if (data_get($temperatureResistance, 'minimum') !== null)
                        <x-dt-dd label="Min">{{ Format::valueWithUnit(data_get($temperatureResistance, 'minimum'), '°C', 1) }}</x-dt-dd>
                    @endif
                    @if (data_get($temperatureResistance, 'maximum') !== null)
                        <x-dt-dd label="Max">{{ Format::valueWithUnit(data_get($temperatureResistance, 'maximum'), '°C', 1) }}</x-dt-dd>
                    @endif
                </x-dl-section>
            @endif

            @if ($radiationResistanceMetrics !== [])
                <x-dl-section title="Radiation Resistance">
                    @foreach ($radiationResistanceMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            {{ Format::valueWithUnit($metric['value'], '', 2) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif
        </x-dl-container>

    </div>
</div>
