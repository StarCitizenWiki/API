@use('App\Support\Format')
@props(['vehicle'])

@php
    use Illuminate\Support\Str;

    $signature = data_get($vehicle, 'signature', []);
    $cooling = data_get($vehicle, 'cooling', []);
    $power = data_get($vehicle, 'power', []);

    $buildMatrixRows = static function (array $shieldsGroups, array $quantumGroups): array {
        $systems = array_values(array_unique(array_merge(array_keys($shieldsGroups), array_keys($quantumGroups))));
        $rows = [];

        foreach ($systems as $system) {
            $shieldsValue = $shieldsGroups[$system] ?? null;
            $quantumValue = $quantumGroups[$system] ?? null;

            if ($shieldsValue === null && $quantumValue === null) {
                continue;
            }

            $rows[] = [
                'label' => Str::headline((string) $system),
                'values' => [
                    $shieldsValue !== null ? Format::numberOrDash($shieldsValue) : '-',
                    $quantumValue !== null ? Format::numberOrDash($quantumValue) : '-',
                ],
            ];
        }

        return $rows;
    };

    $sections = [];

    $emRows = $buildMatrixRows(
        (array) (data_get($signature, 'em_groups_shields') ?? []),
        (array) (data_get($signature, 'em_groups_quantum') ?? [])
    );

    if ($emRows !== []) {
        $sections[] = ['title' => 'EM Groups', 'colHeader' => 'System / EM', 'columns' => ['Shields', 'Quantum'], 'rows' => $emRows];
    }

    $coolingRows = $buildMatrixRows(
        (array) (data_get($cooling, 'used_segments_shields_grouped') ?? []),
        (array) (data_get($cooling, 'used_segments_quantum_grouped') ?? [])
    );

    if ($coolingRows !== []) {
        $sections[] = ['title' => 'Cooling Groups', 'colHeader' => 'System / Segment', 'columns' => ['Shields', 'Quantum'], 'rows' => $coolingRows];
    }

    $powerRows = [];
    foreach ((array) (data_get($power, 'used_segments_grouped') ?? []) as $system => $group) {
        if ($group === null) {
            continue;
        }

        $powerRows[] = ['label' => Str::headline((string) $system), 'value' => Format::numberOrDash($group)];
    }

    if ($powerRows !== []) {
        $sections[] = ['title' => 'Power Groups', 'rows' => $powerRows];
    }
@endphp

<x-data-card title="System Breakdown" :sections="$sections" {{ $attributes }} />
