@use('App\Support\Format')
@props([
    'quantumDrive',
])

@php
    $fuelEfficiency = data_get($quantumDrive, 'fuel_efficiency');
    $fuelConsumption = data_get($quantumDrive, 'fuel_consumption_scu_per_gm');
    $travelTime10GM = data_get($quantumDrive, 'travel_time_10gm', []);
    $travelTimeFormatted = data_get($travelTime10GM, 'formatted');
    $travelTimeSeconds = data_get($travelTime10GM, 'seconds');

    $travelTimeDisplay = null;
    if ($travelTimeSeconds !== null) {
        $travelTimeDisplay = ! empty($travelTimeFormatted) ? $travelTimeFormatted : Format::valueWithUnit($travelTimeSeconds, 's', 2);
    }

    $primaryMetrics = [
        ['label' => 'Fuel Efficiency', 'value' => $fuelEfficiency, 'unit' => 'GM/SCU', 'precision' => 2],
        ['label' => 'Travel Time (10GM)', 'value' => $travelTimeDisplay],
    ];

    $standardJump = data_get($quantumDrive, 'standard_jump', []);
    $splineJump = data_get($quantumDrive, 'spline_jump', []);
@endphp

<x-item-card title="Quantum Drive">
    <x-dl-container>
        <x-slot:head>
            @foreach ($primaryMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @isset($metric['unit'])
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision'] ?? 0) }}
                    @else
                        {{ $metric['value'] }}
                    @endisset
                </x-dt-dd>
            @endforeach
        </x-slot:head>

        <x-dl-section title="Normal Jump">
            @include('components.items.quantum-drive-jump-profile', ['profile' => $standardJump, 'wrap' => false])
        </x-dl-section>

        <x-dl-section title="Spline Jump">
            @include('components.items.quantum-drive-jump-profile', ['profile' => $splineJump, 'wrap' => false])
        </x-dl-section>
    </x-dl-container>
</x-item-card>
