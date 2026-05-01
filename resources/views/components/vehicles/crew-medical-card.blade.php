@props(['vehicle'])

@php
    $seating = data_get($vehicle, 'seating', []);
    $crewStations = data_get($seating, 'crew_stations');
    $ejectionSeats = data_get($seating, 'ejection_seats');
    $escapePods = data_get($seating, 'escape_pods');
    $jumpSeats = data_get($seating, 'jump_seats');
    $beds = data_get($seating, 'beds');
    $medicalBeds = data_get($seating, 'medical_beds');
    $maxMedicalTier = data_get($vehicle, 'max_medical_tier');

    $stationsSection = [
        'label' => 'Crew Stations',
        'help' => 'Stations includes pilot, co-pilot, turret, engineering, and bridge positions',
        'rows' => array_values(array_filter([
            ['label' => 'Total Stations', 'value' => fmt_or_dash($crewStations)],
            ['label' => 'Ejection Seats', 'value' => $ejectionSeats ? fmt_or_dash($ejectionSeats) : '-'],
            ['label' => 'Escape Pods', 'value' => $escapePods !== null ? fmt_or_dash($escapePods) : '-'],
            ['label' => 'Jump Seats', 'value' => $jumpSeats !== null ? fmt_or_dash($jumpSeats) : '-'],
        ], static fn (array $row): bool => $row['value'] !== '-')),
        'render' => $crewStations > 0,
    ];

    $bedsSection = [
        'label' => 'Beds',
        'rows' => array_values(array_filter([
            ['label' => 'Total Beds', 'value' => $beds > 0 ? fmt_or_dash($beds) : '-'],
        ], static fn (array $row): bool => $row['value'] !== '-')),
        'render' => $beds > 0,
    ];

    $medicalSection = [
        'label' => 'Medical',
        'rows' => array_values(array_filter([
            ['label' => 'Max Tier', 'value' => $maxMedicalTier ?? '-'],
        ], static fn (array $row): bool => $row['value'] !== '-')),
        'render' => $maxMedicalTier !== null,
    ];

    if (is_array($medicalBeds)) {
        foreach ($medicalBeds as $tier => $count) {
            $medicalSection['rows'][] = ['label' => "{$tier} Beds", 'value' => (string) $count];
        }
    }

    $sections = array_values(array_filter(
        [$stationsSection, $bedsSection, $medicalSection],
        static fn (array $section): bool => $section['render'],
    ));
@endphp

@if ($sections !== [])
    <section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">Crew & Medical</h2>

            <div class="grid gap-12 lg:grid-cols-3">
                @foreach ($sections as $section)
                    <section class="min-w-0 space-y-3">
                        <div class="text-sm font-semibold text-subtle">
                            {{ $section['label'] }}
                        </div>

                        <dl class="space-y-2">
                            @foreach ($section['rows'] as $row)
                                <div class="grid grid-cols-2 items-start gap-x-3">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">
                                        {{ $row['label'] }}
                                    </dt>
                                    <dd class="text-right text-sm font-semibold text-base-content">
                                        {{ $row['value'] }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                        @if (isset($section['help']))
                            <p class="text-xs text-muted">{{ $section['help'] }}</p>
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </section>
@endif
