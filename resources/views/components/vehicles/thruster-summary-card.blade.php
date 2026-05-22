@use('App\Support\Format')
@props(['vehicle'])

@php
    $propulsion = data_get($vehicle, 'propulsion', []);
    $thrusters = data_get($propulsion, 'thrusters', []);

    $thrusterRows = array_values(array_filter(
        $thrusters,
        static fn (array $t): bool => ($t['type'] ?? null) !== null
    ));

    $sections = [];

    if ($thrusterRows !== []) {
        $sections[] = [
            'fullWidth' => true,
            'columns' => ['Type', 'Count', 'Capacity', 'Accel'],
            'rows' => array_map(static fn (array $t): array => [
                'values' => [
                    $t['type'],
                    (int) ($t['count'] ?? 0),
                    ($t['capacity'] ?? null) !== null ? Format::number((float) $t['capacity'], 2) . ' MN' : '-',
                    ($t['g'] ?? null) !== null ? Format::number((float) $t['g'], 2) . ' G' : '-',
                ],
            ], $thrusterRows),
        ];
    }
@endphp

<x-data-card title="Thruster Groups" :sections="$sections" {{ $attributes }} />
