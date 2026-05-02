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

    $primaryMetrics = array_values(array_filter([
        ['label' => 'Fuel Efficiency', 'value' => $fuelEfficiency, 'unit' => 'GM/SCU', 'precision' => 2],
        ['label' => 'Travel Time (10GM)', 'value' => $travelTimeDisplay],
    ], static fn (array $m): bool => $m['value'] !== null));

    $standardJump = data_get($quantumDrive, 'standard_jump', []);
    $hasStandardJump = is_array($standardJump) && collect($standardJump)->filter(fn ($v) => $v !== null)->isNotEmpty();

    $splineJump = data_get($quantumDrive, 'spline_jump', []);
    $hasSplineJump = is_array($splineJump) && collect($splineJump)->filter(fn ($v) => $v !== null)->isNotEmpty();
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Quantum Drive</h2>

        <x-dl-container>
            <x-slot:head>
                @foreach ($primaryMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        @isset($metric['unit'])
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision'] ?? 0) }}
                        @else
                            {{ $metric['value'] }}
                        @endisset
                    </x-dt-dd>
                @endforeach
            </x-slot:head>

            @if ($hasStandardJump)
                <x-dl-section title="Normal Jump">
                    @include('components.items.quantum-drive-jump-profile', ['profile' => $standardJump, 'wrap' => false])
                </x-dl-section>
            @endif

            @if ($hasSplineJump)
                <x-dl-section title="Spline Jump">
                    @include('components.items.quantum-drive-jump-profile', ['profile' => $splineJump, 'wrap' => false])
                </x-dl-section>
            @endif
        </x-dl-container>
    </div>
</div>
