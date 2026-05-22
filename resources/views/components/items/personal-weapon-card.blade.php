@use('App\Support\Format')
@php use Illuminate\Support\Str; @endphp
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

    $damageTypes = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun'];

    $formatMetric = static function (array $metric): ?array {
        $val = $metric['value'] ?? null;
        if ($val === null) {
            return null;
        }
        if ($val === true || $val === false) {
            return ['label' => $metric['label'], 'value' => $val ? 'Yes' : 'No'];
        }
        if (is_string($val)) {
            return ['label' => $metric['label'], 'value' => $val];
        }
        if (isset($metric['unit']) && $metric['unit'] !== '') {
            return ['label' => $metric['label'], 'value' => Format::valueWithUnit($val, $metric['unit'], $metric['precision'])];
        }

        return ['label' => $metric['label'], 'value' => Format::numberOrDash($val, $metric['precision'] ?? 0)];
    };

    $formatMetrics = static function (array $metrics) use ($formatMetric): array {
        return array_values(array_filter(array_map($formatMetric, $metrics)));
    };

    $sections = [];

    // Info
    $infoRows = array_values(array_filter([
        ['label' => 'Class', 'value' => trim(($class ?? '') . ' ' . ($type ?? '')) ?: null],
        $range !== null ? ['label' => 'Range', 'value' => Format::valueWithUnit($range, 'm', 0)] : null,
        $capacity !== null ? ['label' => 'Capacity', 'value' => Format::valueWithUnit($capacity, 'rounds', 0)] : null,
        $fireMode !== null ? ['label' => 'Fire Mode', 'value' => $fireMode] : null,
    ], static fn (?array $row): bool => $row !== null && ($row['value'] ?? null) !== null && $row['value'] !== ''));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    // Damage
    $damageRows = array_values(array_filter([
        $dpsTotal !== null ? ['label' => 'DPS Total', 'value' => Format::valueWithUnit($dpsTotal, '', 0)] : null,
        $alphaTotal !== null ? ['label' => 'Alpha Total', 'value' => Format::valueWithUnit($alphaTotal, '', 0)] : null,
        $maximum !== null ? ['label' => 'Maximum', 'value' => $maximum . ' per magazine'] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($damageRows !== []) {
        $sections[] = ['title' => 'Damage', 'rows' => $damageRows];
    }

    // Fire Rate
    $fireRateRows = $formatMetrics([
        ['label' => 'RPM', 'value' => $rpm, 'unit' => '/min', 'precision' => 0],
        ['label' => 'Pellets per Shot', 'value' => $pelletsPerShot, 'unit' => '', 'precision' => 0],
    ]);
    if ($fireRateRows !== []) {
        $sections[] = ['title' => 'Fire Rate', 'rows' => $fireRateRows];
    }

    // DPS Breakdown
    $dpsRows = collect($damageTypes)
        ->map(fn (string $t): array => ['label' => Str::headline($t), 'value' => data_get($dps, $t)])
        ->filter(fn (array $m): bool => $m['value'] !== null && $m['value'] > 0)
        ->map(fn (array $m): array => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'], '', 0)])
        ->values()->all();

    if ($dpsRows !== []) {
        $sections[] = ['title' => 'DPS Breakdown', 'rows' => $dpsRows];
    }

    // Alpha Breakdown
    $alphaRows = collect($damageTypes)
        ->map(fn (string $t): array => ['label' => Str::headline($t), 'value' => data_get($alpha, $t)])
        ->filter(fn (array $m): bool => $m['value'] !== null && $m['value'] > 0)
        ->map(fn (array $m): array => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'], '', 0)])
        ->values()->all();

    if ($alphaRows !== []) {
        $sections[] = ['title' => 'Alpha Breakdown', 'rows' => $alphaRows];
    }

    // Hip-fire Spread
    $spreadFields = [
        ['key' => 'first_attack', 'label' => 'First Attack'],
        ['key' => 'per_attack', 'label' => 'Per Attack'],
        ['key' => 'decay', 'label' => 'Decay'],
        ['key' => 'min', 'label' => 'Minimum'],
        ['key' => 'max', 'label' => 'Maximum'],
        ['key' => 'maximum', 'label' => 'Maximum'],
    ];

    $hipRows = [];
    foreach ($spreadFields as $f) {
        $val = data_get($spread, $f['key']);
        if ($val !== null) {
            $hipRows[] = ['label' => $f['label'], 'value' => Format::valueWithUnit($val, 'deg', 1)];
        }
    }
    if ($hipRows !== []) {
        $sections[] = ['title' => 'Hip-fire Spread', 'rows' => $hipRows];
    }

    // ADS Spread
    $adsRows = [];
    foreach ($spreadFields as $f) {
        $val = data_get($adsSpread, $f['key']);
        if ($val !== null) {
            $adsRows[] = ['label' => $f['label'], 'value' => Format::valueWithUnit($val, 'deg', 1)];
        }
    }
    if ($adsRows !== []) {
        $sections[] = ['title' => 'ADS Spread', 'rows' => $adsRows];
    }

    // Heat / Wear
    $heatWearRows = $formatMetrics([
        ['label' => 'Heat Per Second', 'value' => data_get($mode, 'heat_per_second'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Wear Per Second', 'value' => data_get($mode, 'wear_per_second'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Heat Per Shot', 'value' => data_get($mode, 'heat_per_shot'), 'unit' => '', 'precision' => 2],
        ['label' => 'Wear Per Shot', 'value' => data_get($mode, 'wear_per_shot'), 'unit' => '', 'precision' => 2],
    ]);
    if ($heatWearRows !== []) {
        $sections[] = ['title' => 'Heat / Wear', 'rows' => $heatWearRows];
    }

    // Charge Timings
    $chargeTimingRows = $formatMetrics([
        ['label' => 'Time', 'value' => data_get($charge, 'time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharge Time', 'value' => data_get($charge, 'overcharge_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Overcharged Time', 'value' => data_get($charge, 'overcharged_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Cooldown Time', 'value' => data_get($charge, 'cooldown_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Auto Fire', 'value' => data_get($charge, 'auto_fire')],
        ['label' => 'Require Full Charge', 'value' => data_get($charge, 'require_full_charge')],
        ['label' => 'Auto Charge', 'value' => data_get($charge, 'auto_charge')],
    ]);
    if ($chargeTimingRows !== []) {
        $sections[] = ['title' => 'Charge Timings', 'rows' => $chargeTimingRows];
    }

    // Charge Modifiers
    $chargeModRows = $formatMetrics([
        ['label' => 'Damage', 'value' => data_get($chargeModifier, 'damage'), 'unit' => '', 'precision' => 2],
        ['label' => 'Fire Rate', 'value' => data_get($chargeModifier, 'fire_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Ammo Speed', 'value' => data_get($chargeModifier, 'ammo_speed'), 'unit' => '', 'precision' => 2],
        ['label' => 'Fire Rate Override', 'value' => data_get($chargeModifier, 'fire_rate_override'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pellets Override', 'value' => data_get($chargeModifier, 'pellets_override'), 'unit' => '', 'precision' => 0],
        ['label' => 'Burst Shots Override', 'value' => data_get($chargeModifier, 'burst_shots_override'), 'unit' => '', 'precision' => 0],
        ['label' => 'Heat Multiplier', 'value' => data_get($chargeModifier, 'heat_multiplier'), 'unit' => '', 'precision' => 2],
    ]);
    if ($chargeModRows !== []) {
        $sections[] = ['title' => 'Charge Modifiers', 'rows' => $chargeModRows];
    }

    // Mode-specific sections (mutually exclusive)
    if ($fireType === 'beam') {
        $beamRows = $formatMetrics([
            ['label' => 'Charge Up Time', 'value' => data_get($mode, 'charge_up_time'), 'unit' => 's', 'precision' => 2],
            ['label' => 'Charge Down Time', 'value' => data_get($mode, 'charge_down_time'), 'unit' => 's', 'precision' => 2],
            ['label' => 'Full Damage Range', 'value' => data_get($mode, 'full_damage_range'), 'unit' => 'm', 'precision' => 0],
            ['label' => 'Zero Damage Range', 'value' => data_get($mode, 'zero_damage_range'), 'unit' => 'm', 'precision' => 0],
            ['label' => 'Hit Type', 'value' => data_get($mode, 'hit_type')],
            ['label' => 'Hit Radius', 'value' => data_get($mode, 'hit_radius'), 'unit' => 'm', 'precision' => 2],
        ]);
        if ($beamRows !== []) {
            $sections[] = ['title' => 'Beam', 'rows' => $beamRows];
        }
    } elseif ($fireType === 'healingbeam') {
        $healRows = $formatMetrics([
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
            ['label' => 'Battery Drain Per Second', 'value' => data_get($mode, 'battery_drain_per_second'), 'unit' => '/s', 'precision' => 2],
        ]);
        if ($healRows !== []) {
            $sections[] = ['title' => 'Healing Beam', 'rows' => $healRows];
        }
    } elseif (in_array($fireType, ['salvage', 'repair'])) {
        $salvageRows = $formatMetrics([
            ['label' => 'Material Efficiency', 'value' => data_get($mode, 'material_efficiency'), 'unit' => '', 'precision' => 2],
            ['label' => 'Max Health Repair Rate', 'value' => data_get($mode, 'max_health_repair_rate'), 'unit' => '/s', 'precision' => 2],
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
        ]);
        if ($salvageRows !== []) {
            $sections[] = ['title' => 'Salvage / Repair', 'rows' => $salvageRows];
        }
    } elseif ($fireType === 'collectionbeam') {
        $collectRows = $formatMetrics([
            ['label' => 'Minimum Distance', 'value' => data_get($mode, 'minimum_distance'), 'unit' => 'm', 'precision' => 1],
            ['label' => 'Maximum Distance', 'value' => data_get($mode, 'maximum_distance'), 'unit' => 'm', 'precision' => 1],
            ['label' => 'Beam Radius', 'value' => data_get($mode, 'beam_radius'), 'unit' => 'm', 'precision' => 2],
            ['label' => 'Collection Rate', 'value' => data_get($mode, 'collection_rate'), 'unit' => '/s', 'precision' => 2],
            ['label' => 'Energy Draw', 'value' => data_get($mode, 'energy_draw'), 'unit' => '', 'precision' => 2],
            ['label' => 'Mining Extractor Tag', 'value' => data_get($mode, 'mining_extractor_tag')],
        ]);
        if ($collectRows !== []) {
            $sections[] = ['title' => 'Collection Beam', 'rows' => $collectRows];
        }
    } elseif ($fireType === 'tractorbeam') {
        $tractorRows = $formatMetrics([
            ['label' => 'Toggle Mode', 'value' => data_get($mode, 'toggle_mode')],
        ]);
        if ($tractorRows !== []) {
            $sections[] = ['title' => 'Tractor Beam', 'rows' => $tractorRows];
        }
    } elseif ($fireType === 'burst') {
        $burstRows = $formatMetrics([
            ['label' => 'Shot Count', 'value' => data_get($mode, 'shot_count'), 'unit' => '', 'precision' => 0],
            ['label' => 'Cooldown Time', 'value' => data_get($mode, 'cooldown_time'), 'unit' => 's', 'precision' => 2],
        ]);
        if ($burstRows !== []) {
            $sections[] = ['title' => 'Burst', 'rows' => $burstRows];
        }
    } elseif ($fireType === 'rapid') {
        $rapidRows = $formatMetrics([
            ['label' => 'Fire During Spin Up', 'value' => data_get($mode, 'fire_during_spin_up')],
        ]);
        if ($rapidRows !== []) {
            $sections[] = ['title' => 'Rapid', 'rows' => $rapidRows];
        }
    } elseif ($fireType === 'sequence') {
        $sequenceRows = $formatMetrics([
            ['label' => 'Sequence Mode', 'value' => data_get($mode, 'sequence_mode')],
        ]);
        if ($sequenceRows !== []) {
            $sections[] = ['title' => 'Sequence', 'rows' => $sequenceRows];
        }
    }

    // Fire Modes (when multiple)
    if (count($allModes) > 1) {
        $modeRows = [];
        foreach ($allModes as $m) {
            $modeLabel = data_get($m, 'mode', data_get($m, 'type'));
            $modeRpm = Format::numberOrDash(data_get($m, 'rpm'));
            $modeDps = data_get($m, 'damage_per_second');
            $value = $modeRpm . ' RPM';
            if ($modeDps !== null) {
                $value .= ' · ' . Format::numberOrDash($modeDps, 1) . ' DPS';
            }
            $modeRows[] = ['label' => $modeLabel, 'value' => $value];
        }
        if ($modeRows !== []) {
            $sections[] = ['title' => 'Fire Modes', 'rows' => $modeRows];
        }
    }
@endphp

<x-data-card title="Personal Weapon" :sections="$sections" />
