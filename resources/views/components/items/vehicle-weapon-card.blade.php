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

    $mode = data_get($vehicleWeapon, 'modes.0', []);
    $fireType = data_get($mode, 'type');
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
    $allModes = data_get($vehicleWeapon, 'modes', []);

    $primaryMetrics = [
        ['label' => 'Range', 'value' => $range, 'unit' => 'm', 'precision' => 0],
        ['label' => 'RPM', 'value' => $rpm, 'unit' => 'RPM', 'precision' => 0],
    ];

    $damageStatsMetrics = [
        ['label' => 'Alpha', 'value' => $alphaTotal, 'unit' => '', 'precision' => 0],
        ['label' => 'Burst', 'value' => $burst, 'unit' => '', 'precision' => 2],
        ['label' => 'Maximum', 'value' => $maximum, 'unit' => '', 'precision' => 0],
    ];

    $damageTypes = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun'];

    $dpsMetrics = collect($damageTypes)
        ->map(fn (string $type): array => ['label' => Str::headline($type), 'value' => data_get($dps, $type)])
        ->filter(fn (array $m): bool => $m['value'] !== null && $m['value'] > 0)
        ->values()->all();

    $alphaBreakdownMetrics = collect($damageTypes)
        ->map(fn (string $type): array => ['label' => Str::headline($type), 'value' => data_get($alpha, $type)])
        ->filter(fn (array $m): bool => $m['value'] !== null && $m['value'] > 0)
        ->values()->all();

    $spreadMetrics = [
        ['label' => 'First Attack', 'value' => data_get($spread, 'first_attack'), 'unit' => 'deg', 'precision' => 0],
        ['label' => 'Per Attack', 'value' => data_get($spread, 'per_attack'), 'unit' => 'deg', 'precision' => 0],
        ['label' => 'Decay', 'value' => data_get($spread, 'decay'), 'unit' => 'deg/s', 'precision' => 0],
    ];

    $barrelSpinMetrics = [
        ['label' => 'Up', 'value' => data_get($barrelSpinTime, 'up'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Down', 'value' => data_get($barrelSpinTime, 'down'), 'unit' => 's', 'precision' => 2],
    ];

    $heatMetrics = [
        ['label' => 'Overheat Max Shots', 'value' => data_get($heat, 'overheat_max_shots'), 'unit' => '', 'precision' => 0],
        ['label' => 'Overheat Max Time', 'value' => data_get($heat, 'overheat_max_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Per Shot', 'value' => data_get($heat, 'per_shot'), 'unit' => '', 'precision' => 0],
        ['label' => 'Heat Per Second', 'value' => data_get($mode, 'heat_per_second'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Cooling Delay', 'value' => data_get($heat, 'cooling_delay'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Cooling Per Second', 'value' => data_get($heat, 'cooling_per_second'), 'unit' => '', 'precision' => 0],
        ['label' => 'Overheat Cooldown', 'value' => data_get($heat, 'overheat_cooldown'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Wear Per Second', 'value' => data_get($mode, 'wear_per_second'), 'unit' => '/s', 'precision' => 2],
    ];

    $capacitorMetrics = [
        ['label' => 'Max Ammo Load', 'value' => data_get($capacitor, 'max_ammo_load'), 'unit' => '', 'precision' => 0],
        ['label' => 'Regen Per Second', 'value' => data_get($capacitor, 'regen_per_second'), 'unit' => '', 'precision' => 0],
        ['label' => 'Cooldown', 'value' => data_get($capacitor, 'cooldown'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Requested Ammo Load', 'value' => data_get($capacitor, 'requested_ammo_load'), 'unit' => '', 'precision' => 0],
        ['label' => 'Costs Per Shot', 'value' => data_get($capacitor, 'costs_per_shot'), 'unit' => '', 'precision' => 0],
    ];

    $chargeTimingsMetrics = [
        ['label' => 'Time', 'value' => data_get($charge, 'time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharge Time', 'value' => data_get($charge, 'overcharge_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharged Time', 'value' => data_get($charge, 'overcharged_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Cooldown Time', 'value' => data_get($charge, 'cooldown_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Auto Fire', 'value' => data_get($charge, 'auto_fire')],
        ['label' => 'Require Full Charge', 'value' => data_get($charge, 'require_full_charge')],
        ['label' => 'Auto Charge', 'value' => data_get($charge, 'auto_charge')],
        // ['label' => 'Interpolate Bonus', 'value' => data_get($charge, 'interpolate_bonus')],
    ];

    $chargeModMetrics = [
        ['label' => 'Damage', 'value' => data_get($chargeModifier, 'damage'), 'unit' => '', 'precision' => 0],
        ['label' => 'Fire Rate', 'value' => data_get($chargeModifier, 'fire_rate'), 'unit' => '', 'precision' => 0],
        ['label' => 'Ammo Speed', 'value' => data_get($chargeModifier, 'ammo_speed'), 'unit' => '', 'precision' => 0],
        ['label' => 'Fire Rate Override', 'value' => data_get($chargeModifier, 'fire_rate_override'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pellets Override', 'value' => data_get($chargeModifier, 'pellets_override'), 'unit' => '', 'precision' => 0],
        ['label' => 'Burst Shots Override', 'value' => data_get($chargeModifier, 'burst_shots_override'), 'unit' => '', 'precision' => 0],
        ['label' => 'Heat Multiplier', 'value' => data_get($chargeModifier, 'heat_multiplier'), 'unit' => '', 'precision' => 2],
    ];

    $beamMetrics = [
        ['label' => 'Charge Up Time', 'value' => data_get($mode, 'charge_up_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Charge Down Time', 'value' => data_get($mode, 'charge_down_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Full Damage Range', 'value' => data_get($mode, 'full_damage_range'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Zero Damage Range', 'value' => data_get($mode, 'zero_damage_range'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Hit Type', 'value' => data_get($mode, 'hit_type')],
        ['label' => 'Hit Radius', 'value' => data_get($mode, 'hit_radius'), 'unit' => 'm', 'precision' => 2],
        // ['label' => 'Min Energy Draw', 'value' => data_get($mode, 'min_energy_draw'), 'unit' => '', 'precision' => 2],
        // ['label' => 'Max Energy Draw', 'value' => data_get($mode, 'max_energy_draw'), 'unit' => '', 'precision' => 2],
    ];

    $healingBeamMetrics = [
        ['label' => 'Healing Mode', 'value' => data_get($mode, 'healing_mode')],
        ['label' => 'Healing Per Second', 'value' => data_get($mode, 'healing_per_second'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Ammo Per MSCU', 'value' => data_get($mode, 'ammo_per_mscu'), 'unit' => '', 'precision' => 2],
        ['label' => 'Medical Ammo Type', 'value' => data_get($mode, 'medical_ammo_type')],
        ['label' => 'External Healing', 'value' => data_get($mode, 'external_healing')],
        ['label' => 'Toggle', 'value' => data_get($mode, 'toggle')],
        ['label' => 'Max Distance', 'value' => data_get($mode, 'max_distance'), 'unit' => 'm', 'precision' => 1],
        ['label' => 'Max Sensor Distance', 'value' => data_get($mode, 'max_sensor_distance'), 'unit' => 'm', 'precision' => 1],
        ['label' => 'Auto Dosage Modifier', 'value' => data_get($mode, 'auto_dosage_modifier'), 'unit' => '', 'precision' => 2],
        ['label' => 'Healing Break Time', 'value' => data_get($mode, 'healing_break_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Max Dose For Auto Adjustment', 'value' => data_get($mode, 'max_dose_for_auto_adjustment'), 'unit' => '', 'precision' => 2],
        ['label' => 'Battery Drain Per Second', 'value' => data_get($mode, 'battery_drain_per_second'), 'unit' => '/s', 'precision' => 2],
    ];

    $salvageMetrics = [
        ['label' => 'Material Efficiency', 'value' => data_get($mode, 'material_efficiency'), 'unit' => '', 'precision' => 2],
        ['label' => 'Max Health Repair Rate', 'value' => data_get($mode, 'max_health_repair_rate'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Max Damage Map Repair Rate', 'value' => data_get($mode, 'max_damage_map_repair_rate'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Health To Ammo Ratio', 'value' => data_get($mode, 'health_to_ammo_ratio'), 'unit' => '', 'precision' => 2],
        ['label' => 'Ramp Up Time', 'value' => data_get($mode, 'ramp_up_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Ramp Down Time', 'value' => data_get($mode, 'ramp_down_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Max Vehicle Damage Ratio', 'value' => data_get($mode, 'max_vehicle_damage_ratio'), 'unit' => '', 'precision' => 2],
        ['label' => 'Repaired Material Ratio', 'value' => data_get($mode, 'repaired_material_ratio'), 'unit' => '', 'precision' => 2],
        ['label' => 'Can Fire On Full', 'value' => data_get($mode, 'salvage_can_fire_on_full')],
        ['label' => 'Damage Threshold', 'value' => data_get($mode, 'damage_threshold'), 'unit' => '', 'precision' => 2],
        ['label' => 'Hit Radius', 'value' => data_get($mode, 'hit_radius'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Min Energy Draw', 'value' => data_get($mode, 'min_energy_draw'), 'unit' => '', 'precision' => 2],
        ['label' => 'Max Energy Draw', 'value' => data_get($mode, 'max_energy_draw'), 'unit' => '', 'precision' => 2],
    ];

    $collectionBeamMetrics = [
        ['label' => 'Minimum Distance', 'value' => data_get($mode, 'minimum_distance'), 'unit' => 'm', 'precision' => 1],
        ['label' => 'Maximum Distance', 'value' => data_get($mode, 'maximum_distance'), 'unit' => 'm', 'precision' => 1],
        ['label' => 'Beam Radius', 'value' => data_get($mode, 'beam_radius'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Collection Rate', 'value' => data_get($mode, 'collection_rate'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Energy Draw', 'value' => data_get($mode, 'energy_draw'), 'unit' => '', 'precision' => 2],
        ['label' => 'Mining Extractor Tag', 'value' => data_get($mode, 'mining_extractor_tag')],
    ];

    $tractorBeamMetrics = [
        ['label' => 'Toggle Mode', 'value' => data_get($mode, 'toggle_mode')],
    ];

    $burstMetrics = [
        ['label' => 'Shot Count', 'value' => data_get($mode, 'shot_count'), 'unit' => '', 'precision' => 0],
        ['label' => 'Cooldown Time', 'value' => data_get($mode, 'cooldown_time'), 'unit' => 's', 'precision' => 2],
    ];

    $rapidMetrics = [
        ['label' => 'Fire During Spin Up', 'value' => data_get($mode, 'fire_during_spin_up')],
    ];

    $sequenceMetrics = [
        ['label' => 'Sequence Mode', 'value' => data_get($mode, 'sequence_mode')],
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
                    @if ($metric['value'] === true || $metric['value'] === false)
                        {{ $metric['value'] ? 'Yes' : 'No' }}
                    @else
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'] ?? '', $metric['precision'] ?? 2) }}
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Charge Modifiers">
            @foreach ($chargeModMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if ($metric['value'] === true || $metric['value'] === false)
                        {{ $metric['value'] ? 'Yes' : 'No' }}
                    @elseif (isset($metric['unit']) && $metric['unit'] !== '')
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    @else
                        {{ Format::numberOrDash($metric['value'], $metric['precision'] ?? 0) }}
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        @if ($fireType === 'beam' && $beamMetrics !== [])
            <x-dl-section title="Beam">
                @foreach ($beamMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if (isset($metric['unit']) && $metric['unit'] !== '')
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @elseif ($fireType === 'healingbeam' && $healingBeamMetrics !== [])
            <x-dl-section title="Healing Beam">
                @foreach ($healingBeamMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if ($metric['value'] === true || $metric['value'] === false)
                            {{ $metric['value'] ? 'Yes' : 'No' }}
                        @elseif (isset($metric['unit']) && $metric['unit'] !== '')
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @elseif (in_array($fireType, ['salvage', 'repair']) && $salvageMetrics !== [])
            <x-dl-section title="Salvage / Repair">
                @foreach ($salvageMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if ($metric['value'] === true || $metric['value'] === false)
                            {{ $metric['value'] ? 'Yes' : 'No' }}
                        @elseif (isset($metric['unit']) && $metric['unit'] !== '')
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ Format::numberOrDash($metric['value'], $metric['precision'] ?? 0) }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @elseif ($fireType === 'collectionbeam' && $collectionBeamMetrics !== [])
            <x-dl-section title="Collection Beam">
                @foreach ($collectionBeamMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if (isset($metric['unit']) && $metric['unit'] !== '')
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @elseif ($fireType === 'tractorbeam' && $tractorBeamMetrics !== [])
            <x-dl-section title="Tractor Beam">
                @foreach ($tractorBeamMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if ($metric['value'] === true || $metric['value'] === false)
                            {{ $metric['value'] ? 'Yes' : 'No' }}
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @elseif ($fireType === 'burst' && $burstMetrics !== [])
            <x-dl-section title="Burst">
                @foreach ($burstMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @elseif ($fireType === 'rapid' && $rapidMetrics !== [])
            <x-dl-section title="Rapid">
                @foreach ($rapidMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if ($metric['value'] === true || $metric['value'] === false)
                            {{ $metric['value'] ? 'Yes' : 'No' }}
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @elseif ($fireType === 'sequence' && $sequenceMetrics !== [])
            <x-dl-section title="Sequence">
                @foreach ($sequenceMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        {{ $metric['value'] }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @endif

        @if (count($allModes) > 1)
            <x-dl-section title="Fire Modes">
                @foreach ($allModes as $m)
                    <x-dt-dd :label="data_get($m, 'mode', data_get($m, 'type'))" :value="data_get($m, 'damage_per_second')">
                        {{ Format::numberOrDash(data_get($m, 'rpm')) }} <span class="text-xs text-muted">RPM</span>
                        @if (data_get($m, 'damage_per_second') !== null)
                            &middot; {{ Format::numberOrDash(data_get($m, 'damage_per_second'), 1) }} <span class="text-xs text-muted">DPS</span>
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        @endif
    </x-dl-container>
</x-item-card>
