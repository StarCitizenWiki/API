@use('App\Support\Format')
@props([
    'personalWeapon',
])

@php
    $class = data_get($personalWeapon, 'class');
    $type = data_get($personalWeapon, 'type');
    $capacity = data_get($personalWeapon, 'capacity');
    $range = data_get($personalWeapon, 'range');
    $fireMode = data_get($personalWeapon, 'fire_mode');

    $damage = data_get($personalWeapon, 'damage');
    $spread = data_get($personalWeapon, 'spread');
    $adsSpread = data_get($personalWeapon, 'ads_spread');
    $charge = data_get($personalWeapon, 'charge');
    $chargeModifier = data_get($personalWeapon, 'charge_modifier');

    $dpsTotal = data_get($damage, 'dps_total');
    $alphaTotal = data_get($damage, 'alpha_total');
    $maximum = data_get($damage, 'maximum');
    $dps = data_get($damage, 'dps', []);
    $alpha = data_get($damage, 'alpha', []);
    $rpm = data_get($personalWeapon, 'rpm');
    $pelletsPerShot = data_get($personalWeapon, 'pellets_per_shot');

    // Damage metrics
    $damageMetrics = [
        ['label' => 'DPS Total', 'value' => $dpsTotal, 'unit' => '', 'precision' => 0],
        ['label' => 'Alpha Total', 'value' => $alphaTotal, 'unit' => '', 'precision' => 0],
        ['label' => 'Maximum', 'value' => $maximum !== null ? $maximum . ' per magazine' : null, 'unit' => '', 'precision' => 0],
    ];

    // Fire rate metrics
    $fireRateMetrics = [
        ['label' => 'RPM', 'value' => $rpm, 'unit' => '/min', 'precision' => 0],
        ['label' => 'Pellets per Shot', 'value' => $pelletsPerShot, 'unit' => '', 'precision' => 0],
    ];

    // DPS / Alpha breakdown — same 6 damage types
    $damageTypes = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun'];

    $dpsBreakdown = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => Str::headline($type),
            'value' => data_get($dps, $type),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    $alphaBreakdown = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => Str::headline($type),
            'value' => data_get($alpha, $type),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    // Spread metrics — same 5 fields for hip-fire and ADS
    $spreadFields = [
        ['key' => 'first_attack', 'label' => 'First Attack'],
        ['key' => 'per_attack', 'label' => 'Per Attack'],
        ['key' => 'decay', 'label' => 'Decay'],
        ['key' => 'min', 'label' => 'Minimum'],
        ['key' => 'max', 'label' => 'Maximum'],
        ['key' => 'maximum', 'label' => 'Maximum'],
    ];

    $hipSpreadMetrics = array_values(array_filter(
        collect($spreadFields)->map(fn (array $f): array => [
            'label' => $f['label'],
            'value' => data_get($spread, $f['key']),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null,
    ));

    $adsSpreadMetrics = array_values(array_filter(
        collect($spreadFields)->map(fn (array $f): array => [
            'label' => $f['label'],
            'value' => data_get($adsSpread, $f['key']),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null,
    ));

    // Charge metrics
    $chargeMetrics = [
        ['label' => 'Time', 'value' => data_get($charge, 'time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharge Time', 'value' => data_get($charge, 'overcharge_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharged Time', 'value' => data_get($charge, 'overcharged_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Cooldown Time', 'value' => data_get($charge, 'cooldown_time'), 'unit' => 's', 'precision' => 2],
    ];

    $chargeModMetrics = [
        ['label' => 'Damage', 'value' => data_get($chargeModifier, 'damage'), 'unit' => '', 'precision' => 2],
        ['label' => 'Fire Rate', 'value' => data_get($chargeModifier, 'fire_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Ammo Speed', 'value' => data_get($chargeModifier, 'ammo_speed'), 'unit' => '', 'precision' => 2],
    ];

@endphp

<x-item-card title="Personal Weapon">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Class" :value="$class ?? $type">{{ $class }} {{ $type }}</x-dt-dd>
            <x-dt-dd label="Range" :value="$range">{{ Format::valueWithUnit($range, 'm', 0) }}</x-dt-dd>
            <x-dt-dd label="Capacity" :value="$capacity">{{ Format::valueWithUnit($capacity, 'rounds', 0) }}</x-dt-dd>
            <x-dt-dd label="Fire Mode" :value="$fireMode">{{ $fireMode }}</x-dt-dd>
        </x-slot:head>

        <x-dl-section title="Damage">
            @foreach ($damageMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if ($metric['label'] === 'Maximum')
                        {{ $metric['value'] }}
                    @else
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Fire Rate">
            @foreach ($fireRateMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="DPS Breakdown">
            @foreach ($dpsBreakdown as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], '', 0) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Alpha Breakdown">
            @foreach ($alphaBreakdown as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], '', 0) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Hip-fire Spread">
            @foreach ($hipSpreadMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], 'deg', 1) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="ADS Spread">
            @foreach ($adsSpreadMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], 'deg', 1) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Charge Timings">
            @foreach ($chargeMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Charge Modifiers">
            @foreach ($chargeModMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
