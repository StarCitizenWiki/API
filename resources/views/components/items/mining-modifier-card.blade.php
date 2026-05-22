@use('App\Support\Format')
@php use Illuminate\Support\Str; @endphp
@props([
    'miningModifier' => null,
])

@php
    $modifierMap = data_get($miningModifier, 'modifier_map', []);
    $itemType = data_get($miningModifier, 'item_type');
    $type = data_get($miningModifier, 'type');
    $charges = data_get($miningModifier, 'charges');
    $duration = data_get($miningModifier, 'duration');
    $powerModifier = data_get($miningModifier, 'power_modifier');

    $chargesDisplay = $charges !== null ? Format::number((int) $charges, 0) : 'Unlimited';
    $powerDisplay = is_numeric($powerModifier)
        ? Format::valueWithUnit((float) $powerModifier, 'x', 2)
        : Format::numberOrDash($powerModifier);

    $infoRows = array_values(array_filter([
        ['label' => 'Type', 'value' => trim(($itemType ?? '') . ' (' . ($type ?? '') . ')', ' ()')],
        ['label' => 'Charges', 'value' => $chargesDisplay],
        $duration !== null ? ['label' => 'Duration', 'value' => Format::valueWithUnit($duration, 's', 2)] : null,
        $powerModifier !== null ? ['label' => 'Power Modifier', 'value' => $powerDisplay] : null,
    ], static fn (?array $row): bool => $row !== null));

    $modRows = [];
    foreach ($modifierMap as $key => $value) {
        $displayKey = Str::headline($key);
        $displayValue = is_numeric($value) ? Format::number((float) $value, 0) : Format::numberOrDash($value);
        $ddClass = is_numeric($value)
            ? ((float) $value >= 0 ? 'text-success' : 'text-error')
            : '';
        $modRows[] = ['label' => $displayKey, 'value' => $displayValue . '%', 'class' => $ddClass];
    }

    $sections = array_values(array_filter([
        $infoRows !== [] ? ['title' => 'Info', 'rows' => $infoRows] : null,
        $modRows !== [] ? ['title' => 'Modifiers', 'rows' => $modRows] : null,
    ], static fn (?array $s): bool => $s !== null));
@endphp

<x-data-card title="Mining Modifier" :sections="$sections" {{ $attributes }} />
