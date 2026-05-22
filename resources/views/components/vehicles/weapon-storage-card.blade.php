@use('App\Support\Format')
@props(['vehicle'])

@php
    $weaponStorage = data_get($vehicle, 'weapon_storage');
    $suitStorage = data_get($vehicle, 'suit_storage');

    $sections = [];

    if ($suitStorage !== null) {
        $sections[] = [
            'title' => 'Suit Storage',
            'rows' => array_values(array_filter([
                ['label' => 'Lockers', 'value' => data_get($suitStorage, 'lockers')],
                ['label' => 'Total Slots', 'value' => Format::numberOrDash(data_get($suitStorage, 'slots_total'))],
            ], static fn (array $row): bool => $row['value'] !== null)),
        ];
    }

    if ($weaponStorage !== null) {
        $sections[] = [
            'title' => 'Weapon Storage',
            'rows' => array_values(array_filter([
                ['label' => 'Racks', 'value' => data_get($weaponStorage, 'lockers')],
                ['label' => 'Rifle Slots', 'value' => Format::numberOrDash(data_get($weaponStorage, 'slots_rifle'))],
                ['label' => 'Pistol Slots', 'value' => Format::numberOrDash(data_get($weaponStorage, 'slots_pistol'))],
            ], static fn (array $row): bool => $row['value'] !== null)),
        ];
    }
@endphp

@if ($sections !== [])
    <x-data-card title="Storage" :sections="$sections" {{ $attributes }} />
@endif
