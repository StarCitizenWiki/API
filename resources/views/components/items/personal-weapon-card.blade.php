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

    $mode = data_get($personalWeapon, 'modes.0', []);
    $fireType = data_get($mode, 'type');
    $allModes = data_get($personalWeapon, 'modes', []);

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

    $dpsBreakdown = collect($damageTypes)
        ->map(fn (string $type): array => ['label' => Str::headline($type), 'value' => data_get($dps, $type)])
        ->filter(fn (array $m): bool => $m['value'] !== null && $m['value'] > 0)
        ->values()->all();

    $alphaBreakdown = collect($damageTypes)
        ->map(fn (string $type): array => ['label' => Str::headline($type), 'value' => data_get($alpha, $type)])
        ->filter(fn (array $m): bool => $m['value'] !== null && $m['value'] > 0)
        ->values()->all();

    // Spread metrics — same fields for hip-fire and ADS
    $spreadFields = [
        ['key' => 'first_attack', 'label' => 'First Attack'],
        ['key' => 'per_attack', 'label' => 'Per Attack'],
        ['key' => 'decay', 'label' => 'Decay'],
        ['key' => 'min', 'label' => 'Minimum'],
        ['key' => 'max', 'label' => 'Maximum'],
        ['key' => 'maximum', 'label' => 'Maximum'],
    ];

    $hipSpreadMetrics = collect($spreadFields)->map(fn (array $f): array => [
        'label' => $f['label'],
        'value' => data_get($spread, $f['key']),
    ])->all();

    $adsSpreadMetrics = collect($spreadFields)->map(fn (array $f): array => [
        'label' => $f['label'],
        'value' => data_get($adsSpread, $f['key']),
    ])->all();

    // Heat / wear metrics (beam-like modes use per-second, others use per-shot)
    $heatWearMetrics = [
        ['label' => 'Heat Per Second', 'value' => data_get($mode, 'heat_per_second'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Wear Per Second', 'value' => data_get($mode, 'wear_per_second'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Heat Per Shot', 'value' => data_get($mode, 'heat_per_shot'), 'unit' => '', 'precision' => 2],
        ['label' => 'Wear Per Shot', 'value' => data_get($mode, 'wear_per_shot'), 'unit' => '', 'precision' => 2],
    ];

    // Charge timings metrics
    $chargeMetrics = [
        ['label' => 'Time', 'value' => data_get($charge, 'time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharge Time', 'value' => data_get($charge, 'overcharge_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharged Time', 'value' => data_get($charge, 'overcharged_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Cooldown Time', 'value' => data_get($charge, 'cooldown_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Auto Fire', 'value' => data_get($charge, 'auto_fire')],
        ['label' => 'Require Full Charge', 'value' => data_get($charge, 'require_full_charge')],
        ['label' => 'Auto Charge', 'value' => data_get($charge, 'auto_charge')],
    ];

    // Charge modifier metrics
    $chargeModMetrics = [
        ['label' => 'Damage', 'value' => data_get($chargeModifier, 'damage'), 'unit' => '', 'precision' => 2],
        ['label' => 'Fire Rate', 'value' => data_get($chargeModifier, 'fire_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Ammo Speed', 'value' => data_get($chargeModifier, 'ammo_speed'), 'unit' => '', 'precision' => 2],
        ['label' => 'Fire Rate Override', 'value' => data_get($chargeModifier, 'fire_rate_override'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pellets Override', 'value' => data_get($chargeModifier, 'pellets_override'), 'unit' => '', 'precision' => 0],
        ['label' => 'Burst Shots Override', 'value' => data_get($chargeModifier, 'burst_shots_override'), 'unit' => '', 'precision' => 0],
        ['label' => 'Heat Multiplier', 'value' => data_get($chargeModifier, 'heat_multiplier'), 'unit' => '', 'precision' => 2],
    ];

    // Fire-type-specific metrics
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
        // ['label' => 'Max Dose For Auto Adjustment', 'value' => data_get($mode, 'max_dose_for_auto_adjustment'), 'unit' => '', 'precision' => 2],
        ['label' => 'Battery Drain Per Second', 'value' => data_get($mode, 'battery_drain_per_second'), 'unit' => '/s', 'precision' => 2],
    ];

    $salvageMetrics = [
        ['label' => 'Material Efficiency', 'value' => data_get($mode, 'material_efficiency'), 'unit' => '', 'precision' => 2],
        ['label' => 'Max Health Repair Rate', 'value' => data_get($mode, 'max_health_repair_rate'), 'unit' => '/s', 'precision' => 2],
        // ['label' => 'Max Damage Map Repair Rate', 'value' => data_get($mode, 'max_damage_map_repair_rate'), 'unit' => '/s', 'precision' => 2],
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
        <x-dl-section title="Heat / Wear">
            @foreach ($heatWearMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
        <x-dl-section title="Charge Timings">
            @foreach ($chargeMetrics as $metric)
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
