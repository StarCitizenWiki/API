@use('App\Support\Format')
@props([
    'shieldController',
])

@php
    $faceType = data_get($shieldController, 'face_type');

    $primaryMetrics = [
        ['label' => 'Reconfiguration Cooldown', 'value' => data_get($shieldController, 'reconfiguration_cooldown'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Max Reallocation', 'value' => data_get($shieldController, 'max_reallocation'), 'precision' => 0],
        ['label' => 'Electrical Charge Dmg', 'value' => data_get($shieldController, 'max_electrical_charge_damage_rate'), 'unit' => '/s', 'precision' => 1],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Shield Controller</h2>

        <x-dl-container>
            <x-slot:head>
                <x-dt-dd label="Face Type">{{ $faceType ?? '—' }}</x-dt-dd>
                @foreach ($primaryMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        @if (isset($metric['unit']))
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-slot:head>
        </x-dl-container>
    </div>
</div>
