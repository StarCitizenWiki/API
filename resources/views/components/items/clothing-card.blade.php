@use('App\Support\Format')
@props([
    'clothing',
    'temperatureResistance',
    'inventory',
])

@php
    $slot = data_get($clothing, 'slot');
    $temperatureResistance = $temperatureResistance ?? [];
    $scuConverted = data_get($inventory, 'scu_converted');
    $inventoryUnit = data_get($inventory, 'unit', 'SCU');

    $hasTemperatureResistance = data_get($temperatureResistance, 'minimum') !== null || data_get($temperatureResistance, 'maximum') !== null;
    $hasSections = $hasTemperatureResistance;
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Clothing</h2>

        <x-dl-section dlClass="grid gap-x-8 gap-y-2 grid-cols-1 sm:grid-cols-2">
            @if ($slot !== null)
                <x-dt-dd label="Slot">{{ $slot }}</x-dt-dd>
            @endif
            @if ($scuConverted !== null)
                <x-dt-dd label="Inventory">{{ Format::valueWithUnit($scuConverted, $inventoryUnit, 1) }}</x-dt-dd>
            @endif
        </x-dl-section>

        @if ($hasSections)
            <div class="grid gap-8 xl:grid-cols-2">
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
            </div>
        @endif
    </div>
</div>
