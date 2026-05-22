@use('App\Support\Format')
@props([
    'quantumDrive',
])

@php
    $fuelEfficiency = data_get($quantumDrive, 'fuel_efficiency');
    $fuelConsumption = data_get($quantumDrive, 'fuel_consumption_scu_per_gm');
    $travelTime10GM = data_get($quantumDrive, 'travel_time_10gm', []);
    $travelTimeFormatted = data_get($travelTime10GM, 'formatted');
    $travelTimeSeconds = data_get($travelTime10GM, 'seconds');

    $travelTimeDisplay = null;
    if ($travelTimeSeconds !== null) {
        $travelTimeDisplay = ! empty($travelTimeFormatted) ? $travelTimeFormatted : Format::valueWithUnit($travelTimeSeconds, 's', 2);
    }

    $standardJump = data_get($quantumDrive, 'standard_jump', []);
    $splineJump = data_get($quantumDrive, 'spline_jump', []);

    $buildProfileRows = static function (array $profile): array {
        $rows = [];

        if (data_get($profile, 'drive_speed') !== null) {
            $rows[] = ['label' => 'Drive Speed', 'value' => Format::valueWithUnit(data_get($profile, 'drive_speed'), 'm/s', 0)];
        }
        if (data_get($profile, 'cooldown_time') !== null) {
            $rows[] = ['label' => 'Cooldown Time', 'value' => Format::valueWithUnit(data_get($profile, 'cooldown_time'), 's', 1)];
        }
        if (data_get($profile, 'stage_one_accel_rate') !== null) {
            $rows[] = ['label' => 'Stage One', 'value' => Format::valueWithUnit(data_get($profile, 'stage_one_accel_rate'), 'm/s²', 0)];
        }
        if (data_get($profile, 'stage_two_accel_rate') !== null) {
            $rows[] = ['label' => 'Stage Two', 'value' => Format::valueWithUnit(data_get($profile, 'stage_two_accel_rate'), 'm/s²', 0)];
        }
        if (data_get($profile, 'engage_speed') !== null) {
            $rows[] = ['label' => 'Engage Speed', 'value' => (int) data_get($profile, 'engage_speed') . ' m/s'];
        }
        if (data_get($profile, 'interdiction_effect_time') !== null) {
            $rows[] = ['label' => 'Interdiction Effect Time', 'value' => Format::valueWithUnit(data_get($profile, 'interdiction_effect_time'), 's', 2)];
        }
        if (data_get($profile, 'calibration_rate') !== null) {
            $rows[] = ['label' => 'Calibration Rate', 'value' => (string) ((int) data_get($profile, 'calibration_rate'))];
        }
        if (data_get($profile, 'min_calibration_requirement') !== null) {
            $rows[] = ['label' => 'Calibration Requirement', 'value' => Format::range(data_get($profile, 'min_calibration_requirement'), data_get($profile, 'max_calibration_requirement'), '', 0)];
        }
        if (data_get($profile, 'calibration_process_angle_limit') !== null) {
            $rows[] = ['label' => 'Calibration Angle', 'value' => Format::range(data_get($profile, 'calibration_process_angle_limit'), data_get($profile, 'calibration_warning_angle_limit'), 'deg', 1)];
        }
        if (data_get($profile, 'calibration_delay_in_seconds') !== null) {
            $rows[] = ['label' => 'Calibration Delay', 'value' => Format::valueWithUnit(data_get($profile, 'calibration_delay_in_seconds'), 's', 1)];
        }
        if (data_get($profile, 'spool_up_time') !== null) {
            $rows[] = ['label' => 'Spool Up Time', 'value' => Format::valueWithUnit(data_get($profile, 'spool_up_time'), 's', 1)];
        }

        return $rows;
    };

    $infoRows = array_values(array_filter([
        ['label' => 'Fuel Efficiency', 'value' => Format::valueWithUnit($fuelEfficiency, 'GM/SCU', 2)],
        ['label' => 'Travel Time (10GM)', 'value' => $travelTimeDisplay],
    ], static fn (array $row): bool => $row['value'] !== null));

    $standardRows = $buildProfileRows($standardJump);
    $splineRows = $buildProfileRows($splineJump);

    $sections = array_values(array_filter([
        $infoRows !== [] ? ['title' => 'Info', 'rows' => $infoRows] : null,
        $standardRows !== [] ? ['title' => 'Normal Jump', 'rows' => $standardRows] : null,
        $splineRows !== [] ? ['title' => 'Spline Jump', 'rows' => $splineRows] : null,
    ], static fn (?array $s): bool => $s !== null));
@endphp

<x-data-card title="Quantum Drive" :sections="$sections" {{ $attributes }} />
