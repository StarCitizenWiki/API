@use('App\Support\Format')
@props([
    'vehicleWeapon',
])

@php
    $class = data_get($vehicleWeapon, 'class');
    $type = data_get($vehicleWeapon, 'type');
    $capacity = data_get($vehicleWeapon, 'capacity');
    $range = data_get($vehicleWeapon, 'range');
    $rpm = data_get($vehicleWeapon, 'rpm');

    $damage = data_get($vehicleWeapon, 'damage', []);
    $alphaTotal = data_get($damage, 'alpha_total');
    $maximum = data_get($damage, 'maximum');
    $burst = data_get($damage, 'burst');
    $dps = data_get($damage, 'dps', []);
    $alpha = data_get($damage, 'alpha', []);

    $spread = data_get($vehicleWeapon, 'spread', []);
    $barrelSpinTime = data_get($vehicleWeapon, 'barrel_spin_time', []);
    $heat = data_get($vehicleWeapon, 'heat', []);
    $capacitor = data_get($vehicleWeapon, 'capacitor', []);
    $charge = data_get($vehicleWeapon, 'charge', []);
    $chargeModifier = data_get($vehicleWeapon, 'charge_modifier', []);

    // Primary metrics — Range and RPM (Class/Capacity use special rendering)
    $primaryMetrics = [
        ['label' => 'Range', 'value' => $range, 'unit' => 'm', 'precision' => 0],
        ['label' => 'RPM', 'value' => $rpm, 'unit' => 'RPM', 'precision' => 0],
    ];

    // Damage stats metrics — Alpha, Burst, Maximum (Maximum uses special rendering)
    $damageStatsMetrics = [
        ['label' => 'Alpha', 'value' => $alphaTotal, 'unit' => '', 'precision' => 0],
        ['label' => 'Burst', 'value' => $burst, 'unit' => '', 'precision' => 2],
        ['label' => 'Maximum', 'value' => $maximum, 'unit' => '', 'precision' => 0],
    ];

    // DPS / Alpha breakdown — 6 damage types
    $damageTypes = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun'];

    $dpsMetrics = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => Str::headline($type),
            'value' => data_get($dps, $type),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    $alphaBreakdownMetrics = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => Str::headline($type),
            'value' => data_get($alpha, $type),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    // Spread metrics (Min/Max range uses special fmt_range rendering)
    $spreadMetrics = [
        ['label' => 'First Attack', 'value' => data_get($spread, 'first_attack'), 'unit' => 'deg', 'precision' => 0],
        ['label' => 'Per Attack', 'value' => data_get($spread, 'per_attack'), 'unit' => 'deg', 'precision' => 0],
        ['label' => 'Decay', 'value' => data_get($spread, 'decay'), 'unit' => 'deg/s', 'precision' => 0],
    ];

    // Barrel spin metrics
    $barrelSpinMetrics = [
        ['label' => 'Up', 'value' => data_get($barrelSpinTime, 'up'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Down', 'value' => data_get($barrelSpinTime, 'down'), 'unit' => 's', 'precision' => 2],
    ];

    // Heat metrics
    $heatMetrics = [
        ['label' => 'Overheat Max Shots', 'value' => data_get($heat, 'overheat_max_shots'), 'unit' => '', 'precision' => 0],
        ['label' => 'Overheat Max Time', 'value' => data_get($heat, 'overheat_max_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Per Shot', 'value' => data_get($heat, 'per_shot'), 'unit' => '', 'precision' => 0],
        ['label' => 'Cooling Delay', 'value' => data_get($heat, 'cooling_delay'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Cooling Per Second', 'value' => data_get($heat, 'cooling_per_second'), 'unit' => '', 'precision' => 0],
        ['label' => 'Overheat Cooldown', 'value' => data_get($heat, 'overheat_cooldown'), 'unit' => 's', 'precision' => 2],
    ];

    // Capacitor metrics
    $capacitorMetrics = [
        ['label' => 'Max Ammo Load', 'value' => data_get($capacitor, 'max_ammo_load'), 'unit' => '', 'precision' => 0],
        ['label' => 'Regen Per Second', 'value' => data_get($capacitor, 'regen_per_second'), 'unit' => '', 'precision' => 0],
        ['label' => 'Cooldown', 'value' => data_get($capacitor, 'cooldown'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Requested Ammo Load', 'value' => data_get($capacitor, 'requested_ammo_load'), 'unit' => '', 'precision' => 0],
        ['label' => 'Costs Per Shot', 'value' => data_get($capacitor, 'costs_per_shot'), 'unit' => '', 'precision' => 0],
    ];

    // Charge timings metrics
    $chargeTimingsMetrics = [
        ['label' => 'Time', 'value' => data_get($charge, 'time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharge Time', 'value' => data_get($charge, 'overcharge_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharged Time', 'value' => data_get($charge, 'overcharged_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Cooldown Time', 'value' => data_get($charge, 'cooldown_time'), 'unit' => 's', 'precision' => 2],
    ];

    // Charge modifier metrics
    $chargeModMetrics = [
        ['label' => 'Damage', 'value' => data_get($chargeModifier, 'damage'), 'unit' => '', 'precision' => 0],
        ['label' => 'Fire Rate', 'value' => data_get($chargeModifier, 'fire_rate'), 'unit' => '', 'precision' => 0],
        ['label' => 'Ammo Speed', 'value' => data_get($chargeModifier, 'ammo_speed'), 'unit' => '', 'precision' => 0],
    ];

@endphp

<x-item-card title="Vehicle Weapon">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Class" :value="$class ?? $type">{{ $class }} {{ $type }}</x-dt-dd>
            <x-dt-dd label="Capacity" :value="$capacity">{{ $capacity === 0 ? 'Infinite' : Format::valueWithUnit($capacity, 'rounds', 0) }}</x-dt-dd>
            @foreach ($primaryMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-slot:head>

        <x-dl-section title="Damage">
            @foreach ($damageStatsMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if ($metric['label'] === 'Maximum')
                        {{ $metric['value'] === 'Infinite' || $metric['value'] === null ? ($metric['value'] ?? '-') : Format::compact($metric['value']) }}
                    @else
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="DPS Breakdown">
            @foreach ($dpsMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], '', 0) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Alpha Breakdown">
            @foreach ($alphaBreakdownMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], '', 0) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Spread">
            <x-dt-dd label="Min/Max" :value="data_get($spread, 'min') ?? data_get($spread, 'max')">{{ Format::range(data_get($spread, 'min'), data_get($spread, 'max'), 'deg', 0) }}</x-dt-dd>
            <x-dt-dd label="Min/Max" :value="data_get($spread, 'minimum') ?? data_get($spread, 'maximum')">{{ Format::range(data_get($spread, 'minimum'), data_get($spread, 'maximum'), 'deg', 0) }}</x-dt-dd>
            @foreach ($spreadMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Barrel Spin Time">
            @foreach ($barrelSpinMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Heat">
            @foreach ($heatMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Capacitor">
            @foreach ($capacitorMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Charge Timings">
            @foreach ($chargeTimingsMetrics as $metric)
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
