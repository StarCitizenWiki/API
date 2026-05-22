@use('App\Support\Format')
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
            ['label' => 'Total Stations', 'value' => Format::numberOrDash($crewStations)],
            ['label' => 'Ejection Seats', 'value' => $ejectionSeats ? Format::numberOrDash($ejectionSeats) : '-'],
            ['label' => 'Escape Pods', 'value' => $escapePods !== null ? Format::numberOrDash($escapePods) : '-'],
            ['label' => 'Jump Seats', 'value' => $jumpSeats !== null ? Format::numberOrDash($jumpSeats) : '-'],
        ], static fn (array $row): bool => $row['value'] !== '-')),
        'render' => $crewStations > 0,
    ];

    $bedsSection = [
        'label' => 'Beds',
        'rows' => array_values(array_filter([
            ['label' => 'Total Beds', 'value' => $beds > 0 ? Format::numberOrDash($beds) : '-'],
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
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">Crew & Medical</h2>

            <div class="grid gap-6 grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
                @foreach ($sections as $section)
                    <div class="min-w-0 space-y-3">
                        <x-dl-section :title="$section['label']">
                            @foreach ($section['rows'] as $row)
                                <x-dt-dd :label="$row['label']">{{ $row['value'] }}</x-dt-dd>
                            @endforeach
                        </x-dl-section>

                        @if (isset($section['help']))
                            <p class="text-xs text-muted">{{ $section['help'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
