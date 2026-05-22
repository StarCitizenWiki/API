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

    $sections = [];

    $stationsRows = array_values(array_filter([
        ['label' => 'Total Stations', 'value' => Format::numberOrDash($crewStations)],
        ['label' => 'Ejection Seats', 'value' => $ejectionSeats ? Format::numberOrDash($ejectionSeats) : null],
        ['label' => 'Escape Pods', 'value' => $escapePods !== null ? Format::numberOrDash($escapePods) : null],
        ['label' => 'Jump Seats', 'value' => $jumpSeats !== null ? Format::numberOrDash($jumpSeats) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($crewStations > 0 && $stationsRows !== []) {
        $sections[] = [
            'title' => 'Crew Stations',
            'help' => 'Stations includes pilot, co-pilot, turret, engineering, and bridge positions',
            'rows' => $stationsRows,
        ];
    }

    $bedsRows = array_values(array_filter([
        ['label' => 'Total Beds', 'value' => $beds > 0 ? Format::numberOrDash($beds) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($beds > 0 && $bedsRows !== []) {
        $sections[] = ['title' => 'Beds', 'rows' => $bedsRows];
    }

    $medRows = array_values(array_filter([
        ['label' => 'Max Tier', 'value' => $maxMedicalTier],
    ], static fn (array $row): bool => $row['value'] !== null));

    if (is_array($medicalBeds)) {
        foreach ($medicalBeds as $tier => $count) {
            $medRows[] = ['label' => "{$tier} Beds", 'value' => (string) $count];
        }
    }

    if ($maxMedicalTier !== null && $medRows !== []) {
        $sections[] = ['title' => 'Medical', 'rows' => $medRows];
    }
@endphp

<x-data-card title="Crew & Medical" :sections="$sections" {{ $attributes }} />
