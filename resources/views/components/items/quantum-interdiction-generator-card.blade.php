@use('App\Support\Format')
@props([
    'quantumInterdictionGenerator' => null,
])

@php
    $jamming = data_get($quantumInterdictionGenerator, 'jamming', []);
    $pulse = data_get($quantumInterdictionGenerator, 'pulse', []);
    $powerFractions = data_get($quantumInterdictionGenerator, 'power_fractions', []);

    $jammingRange = data_get($jamming, 'range');
    $pulseRadius = data_get($pulse, 'radius');
    $chargeTime = data_get($pulse, 'charge_time');
    $dischargeTime = data_get($pulse, 'discharge_time');
    $cooldownTime = data_get($pulse, 'cooldown_time');

    $sections = [];

    // Info
    $infoRows = array_values(array_filter([
        ['label' => 'Jamming Range', 'value' => $jammingRange !== null ? Format::valueWithUnit($jammingRange, 'm', 0) : null],
        ['label' => 'Pulse Radius', 'value' => $pulseRadius !== null ? Format::valueWithUnit($pulseRadius, 'm', 0) : null],
        ['label' => 'Charge Time', 'value' => $chargeTime !== null ? Format::valueWithUnit($chargeTime, 's', 1) : null],
        ['label' => 'Discharge Time', 'value' => $dischargeTime !== null ? Format::valueWithUnit($dischargeTime, 's', 1) : null],
        ['label' => 'Cooldown Time', 'value' => $cooldownTime !== null ? Format::valueWithUnit($cooldownTime, 's', 1) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    // Power Fractions
    $powerFractionRows = array_values(array_filter([
        ['label' => 'Base Power Fraction', 'value' => data_get($powerFractions, 'base') !== null ? Format::numberOrDash(data_get($powerFractions, 'base'), 2) : null],
        ['label' => 'Pulse Power Fraction', 'value' => data_get($powerFractions, 'pulse') !== null ? Format::numberOrDash(data_get($powerFractions, 'pulse'), 2) : null],
        ['label' => 'Jammer Power Fraction', 'value' => data_get($powerFractions, 'jammer') !== null ? Format::numberOrDash(data_get($powerFractions, 'jammer'), 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($powerFractionRows !== []) {
        $sections[] = ['title' => 'Power Fractions', 'rows' => $powerFractionRows];
    }

    // Jamming
    $jammingRows = array_values(array_filter([
        ['label' => 'Green Zone Check Range', 'value' => data_get($jamming, 'green_zone_check_range') !== null ? Format::valueWithUnit(data_get($jamming, 'green_zone_check_range'), 'm', 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($jammingRows !== []) {
        $sections[] = ['title' => 'Jamming', 'rows' => $jammingRows];
    }

    // Pulse Timing
    $pulseTimingRows = array_values(array_filter([
        ['label' => 'Activation Phase Duration', 'value' => data_get($pulse, 'activation_phase_duration') !== null ? Format::valueWithUnit(data_get($pulse, 'activation_phase_duration'), 's', 2) : null],
        ['label' => 'Disperse Charge Time', 'value' => data_get($pulse, 'disperse_charge_time') !== null ? Format::valueWithUnit(data_get($pulse, 'disperse_charge_time'), 's', 2) : null],
        ['label' => 'Decrease Charge Rate Time', 'value' => data_get($pulse, 'decrease_charge_rate_time') !== null ? Format::valueWithUnit(data_get($pulse, 'decrease_charge_rate_time'), 's', 2) : null],
        ['label' => 'Increase Charge Rate Time', 'value' => data_get($pulse, 'increase_charge_rate_time') !== null ? Format::valueWithUnit(data_get($pulse, 'increase_charge_rate_time'), 's', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($pulseTimingRows !== []) {
        $sections[] = ['title' => 'Pulse Timing', 'rows' => $pulseTimingRows];
    }

    // Max Power Draw
    $maxPowerDrawRows = array_values(array_filter([
        ['label' => 'Jamming Max Power Draw', 'value' => data_get($jamming, 'max_power_draw') !== null ? Format::numberOrDash(data_get($jamming, 'max_power_draw'), 2) : null],
        ['label' => 'Pulse Max Power Draw', 'value' => data_get($pulse, 'max_power_draw') !== null ? Format::numberOrDash(data_get($pulse, 'max_power_draw'), 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($maxPowerDrawRows !== []) {
        $sections[] = ['title' => 'Max Power Draw', 'rows' => $maxPowerDrawRows];
    }

    // Advanced Power & Range
    $advancedRows = array_values(array_filter([
        ['label' => 'Stop Charging Power Fraction', 'value' => data_get($pulse, 'stop_charging_power_fraction') !== null ? Format::numberOrDash(data_get($pulse, 'stop_charging_power_fraction'), 2) : null],
        ['label' => 'Max Charge Rate Power Fraction', 'value' => data_get($pulse, 'max_charge_rate_power_fraction') !== null ? Format::numberOrDash(data_get($pulse, 'max_charge_rate_power_fraction'), 2) : null],
        ['label' => 'Active Power Fraction', 'value' => data_get($pulse, 'active_power_fraction') !== null ? Format::numberOrDash(data_get($pulse, 'active_power_fraction'), 2) : null],
        ['label' => 'Tethering Power Fraction', 'value' => data_get($pulse, 'tethering_power_fraction') !== null ? Format::numberOrDash(data_get($pulse, 'tethering_power_fraction'), 2) : null],
        ['label' => 'Pulse Green Zone Check Range', 'value' => data_get($pulse, 'green_zone_check_range') !== null ? Format::valueWithUnit(data_get($pulse, 'green_zone_check_range'), 'm', 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($advancedRows !== []) {
        $sections[] = ['title' => 'Advanced Power & Range', 'rows' => $advancedRows];
    }
@endphp

<x-data-card title="Quantum Interdiction Generator" :sections="$sections" {{ $attributes }} />
