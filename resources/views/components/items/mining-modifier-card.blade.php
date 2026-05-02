@use('App\Support\Format')
@props([
    'miningModifier' => null,
])

@php
    $modifierMap = data_get($miningModifier, 'modifier_map', []);
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Mining Modifier</h2>

        <x-dl-container>
            <x-slot:head>
                <x-dt-dd label="Type">{{ data_get($miningModifier, 'item_type') }} ({{ data_get($miningModifier, 'type') }})</x-dt-dd>

                <x-dt-dd label="Charges">
                    @if (data_get($miningModifier, 'charges') !== null)
                        {{ Format::number((int)data_get($miningModifier, 'charges'), 0) }}
                    @else
                        Unlimited
                    @endif
                </x-dt-dd>

                <x-dt-dd label="Duration">{{ Format::valueWithUnit(data_get($miningModifier, 'duration'), 's', 2) }}</x-dt-dd>

                <x-dt-dd label="Power Modifier">
                    @if (is_numeric(data_get($miningModifier, 'power_modifier')))
                        {{ Format::valueWithUnit((float)data_get($miningModifier, 'power_modifier'), 'x', 2) }}
                    @else
                        {{ Format::numberOrDash(data_get($miningModifier, 'power_modifier')) }}
                    @endif
                </x-dt-dd>
            </x-slot:head>

            <x-dl-section title="Modifiers">
                @foreach ($modifierMap as $key => $value)
                    @php
                        $displayKey = \Illuminate\Support\Str::headline($key);
                        $displayValue = is_numeric($value) ? Format::number((float) $value, 0) : Format::numberOrDash($value);
                        $ddClass = is_numeric($value)
                            ? ((float) $value >= 0 ? 'text-success' : 'text-error')
                            : '';
                    @endphp
                    <x-dt-dd label="{{ $displayKey }}" :ddClass="$ddClass">{{ $displayValue }}%</x-dt-dd>
                @endforeach
            </x-dl-section>
        </x-dl-container>
    </div>
</div>
