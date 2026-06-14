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

    $addFormattedRow = static function (array &$rows, array $profile, string $key, string $label, string $unit, string $formattedKey): void {
        if (data_get($profile, $key) === null) {
            return;
        }
        $formatted = data_get($profile, $formattedKey) ?? Format::valueWithUnit(data_get($profile, $key), $unit, 0);
        $raw = Format::number(data_get($profile, $key), 0).' '.$unit;
        $rows[] = ['label' => $label, 'value' => $formatted, 'title' => $formatted === $raw ? null : $raw];
    };

    $buildProfileRows = static function (array $profile) use (&$addFormattedRow): array {
        $rows = [];

        $addFormattedRow($rows, $profile, 'drive_speed', 'Drive Speed', 'm/s', 'drive_speed_formatted');

        if (data_get($profile, 'cooldown_time') !== null) {
            $rows[] = ['label' => 'Cooldown Time', 'value' => Format::valueWithUnit(data_get($profile, 'cooldown_time'), 's', 1)];
        }

        $addFormattedRow($rows, $profile, 'stage_one_accel_rate', 'Stage One', 'm/s²', 'stage_one_accel_rate_formatted');
        $addFormattedRow($rows, $profile, 'stage_two_accel_rate', 'Stage Two', 'm/s²', 'stage_two_accel_rate_formatted');
        $addFormattedRow($rows, $profile, 'engage_speed', 'Engage Speed', 'm/s', 'engage_speed_formatted');
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

    $jumpRangeFormatted = data_get($quantumDrive, 'jump_range_formatted');
    $disconnectRangeFormatted = data_get($quantumDrive, 'disconnect_range_formatted');

    $infoRows = array_values(array_filter([
        ['label' => 'Fuel Efficiency', 'value' => Format::valueWithUnit($fuelEfficiency, 'GM/SCU', 2)],
        ['label' => 'Travel Time (10GM)', 'value' => $travelTimeDisplay],
        $jumpRangeFormatted !== null ? ['label' => 'Jump Range', 'value' => $jumpRangeFormatted, 'title' => $jumpRangeFormatted !== Format::number(data_get($quantumDrive, 'jump_range'), 0).' m' ? Format::number(data_get($quantumDrive, 'jump_range'), 0).' m' : null] : null,
        $disconnectRangeFormatted !== null ? ['label' => 'Disconnect Range', 'value' => $disconnectRangeFormatted, 'title' => Format::number(data_get($quantumDrive, 'disconnect_range'), 0).' m'] : null,
    ], static fn (array $row): bool => $row['value'] !== null));

    $standardRows = $buildProfileRows($standardJump);
    $splineRows = $buildProfileRows($splineJump);

    $allLabels = collect($standardRows)->pluck('label')->merge(collect($splineRows)->pluck('label'))->unique()->values();
    $standardByKey = collect($standardRows)->keyBy('label');
    $splineByKey = collect($splineRows)->keyBy('label');
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
    <div class="card-body gap-3 p-4 sm:p-5">
        <h2 class="card-title text-base">Quantum Drive</h2>

        {{-- Info + Calculator side by side --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if ($infoRows !== [])
                <div>
                    <div class="flex items-baseline justify-between border-b border-base-200 pb-1 mb-2">
                        <h3 class="text-xs font-medium uppercase tracking-wider text-muted">Info</h3>
                    </div>
                    <dl class="space-y-1">
                        @foreach ($infoRows as $row)
                            <div class="flex items-baseline justify-between gap-2">
                                <dt class="text-xs text-subtle">{{ $row['label'] }}</dt>
                                <dd class="text-sm font-semibold" @if (($row['title'] ?? null) !== null) title="{{ $row['title'] }}" @endif>{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            <div>
                <div class="flex items-baseline justify-between border-b border-base-200 pb-1 mb-2">
                    <h3 class="text-xs font-medium uppercase tracking-wider text-muted">Route Planner</h3>
                </div>
                <x-quantum-drive-travel-calc :quantum-drive="$quantumDrive"/>
            </div>
        </div>

        @if ($standardRows !== [] || $splineRows !== [])
            <details class="collapse collapse-arrow border border-base-200 bg-base-100">
                <summary class="collapse-title min-h-0 py-2 pr-10 text-xs font-medium uppercase tracking-wider text-muted">Jump Profiles</summary>
                <div class="collapse-content border-t border-base-300 px-4 pb-3 pt-3">
                    <dl class="grid grid-cols-3 gap-x-2 gap-y-1">
                        <dt class="text-xs text-muted"></dt>
                        <dt class="text-right text-xs text-muted">Normal</dt>
                        <dt class="text-right text-xs text-muted">Spline</dt>

                        @foreach ($allLabels as $label)
                            @php
                                $std = $standardByKey->get($label);
                                $spl = $splineByKey->get($label);
                            @endphp
                            <dt class="text-xs text-subtle">{{ $label }}</dt>
                            <dd class="text-right text-sm font-semibold tabular-nums" @if ($std && ($std['title'] ?? null) !== null) title="{{ $std['title'] }}" @endif>{{ $std['value'] ?? '-' }}</dd>
                            <dd class="text-right text-sm font-semibold tabular-nums" @if ($spl && ($spl['title'] ?? null) !== null) title="{{ $spl['title'] }}" @endif>{{ $spl['value'] ?? '-' }}</dd>
                        @endforeach
                    </dl>
                </div>
            </details>
        @endif
    </div>
</section>
