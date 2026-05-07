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

@endphp

<x-item-card title="Mining Modifier">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Type" :value="$itemType ?? $type">{{ $itemType }} ({{ $type }})</x-dt-dd>

            <x-dt-dd label="Charges" :value="$charges ?? true">
                @if ($charges !== null)
                    {{ Format::number((int) $charges, 0) }}
                @else
                    Unlimited
                @endif
            </x-dt-dd>

            <x-dt-dd label="Duration" :value="$duration">{{ Format::valueWithUnit($duration, 's', 2) }}</x-dt-dd>

            <x-dt-dd label="Power Modifier" :value="$powerModifier">
                @if (is_numeric($powerModifier))
                    {{ Format::valueWithUnit((float) $powerModifier, 'x', 2) }}
                @else
                    {{ Format::numberOrDash($powerModifier) }}
                @endif
            </x-dt-dd>
        </x-slot:head>

        <x-dl-section title="Modifiers">
            @foreach ($modifierMap as $key => $value)
                @php
                    $displayKey = Str::headline($key);
                    $displayValue = is_numeric($value) ? Format::number((float) $value, 0) : Format::numberOrDash($value);
                    $ddClass = is_numeric($value)
                        ? ((float) $value >= 0 ? 'text-success' : 'text-error')
                        : '';
                @endphp
                <x-dt-dd label="{{ $displayKey }}" :ddClass="$ddClass">{{ $displayValue }}%</x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
