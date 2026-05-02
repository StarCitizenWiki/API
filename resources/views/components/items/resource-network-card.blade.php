@use('App\Support\Format')
@props([
    'resourceNetwork',
    'itemType',
])

@php
    $usage = data_get($resourceNetwork, 'usage');
    $generation = data_get($resourceNetwork, 'generation');

    $primaryMetrics = array_values(array_filter([
        $itemType !== 'PowerPlant' ? ['label' => 'Power Usage', 'value' => Format::range(data_get($usage, 'power.minimum'), data_get($usage, 'power.maximum'), 'Segments')] : null,
        $itemType !== 'Cooler' ? ['label' => 'Coolant Usage', 'value' => Format::range(data_get($usage, 'coolant.minimum'), data_get($usage, 'coolant.maximum'), 'Segments')] : null,
        $itemType === 'PowerPlant' && data_get($generation, 'power') !== null ? ['label' => 'Power Generation', 'value' => Format::valueWithUnit(data_get($generation, 'power'), 'Segments', 0)] : null,
        $itemType === 'Cooler' && data_get($generation, 'coolant') !== null ? ['label' => 'Coolant Generation', 'value' => Format::valueWithUnit(data_get($generation, 'coolant'), 'Segments', 0)] : null,
    ], static fn (?array $m): bool => $m !== null));

    $repair = data_get($resourceNetwork, 'repair');

    $repairMetrics = array_values(array_filter([
        data_get($repair, 'max_repair_count') !== null ? ['label' => 'Repair Count', 'value' => Format::valueWithUnit(data_get($repair, 'max_repair_count'), 'x', 0)] : null,
        data_get($repair, 'time_to_repair') !== null ? ['label' => 'Repair Time', 'value' => Format::valueWithUnit(data_get($repair, 'time_to_repair'), 's', 0)] : null,
        data_get($repair, 'health_ratio') !== null ? ['label' => 'Health Ratio', 'value' => Format::valueWithUnit(data_get($repair, 'health_ratio') * 100, '%', 1)] : null,
    ], static fn (?array $m): bool => $m !== null));

    $states = data_get($resourceNetwork, 'states', []);
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Resource Network</h2>

        <x-dl-container>
            <x-slot:head>
                @foreach ($primaryMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">{{ $metric['value'] }}</x-dt-dd>
                @endforeach
            </x-slot:head>

            @if ($repairMetrics !== [])
            <x-dl-details title="Self-Repair">
                @foreach ($repairMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">{{ $metric['value'] }}</x-dt-dd>
                @endforeach
            </x-dl-details>
            @endif

            @foreach ($states as $state)
                @php
                    $stateDeltas = data_get($state, 'deltas', []);
                    $statePowerRanges = data_get($state, 'power_ranges', []);
                @endphp

                @if (count($stateDeltas) > 0 || (is_array($statePowerRanges) && count($statePowerRanges) > 0))
                <x-dl-details title="{{ $state['name']. ' State' }}">
                    @if (count($stateDeltas) > 0)
                        <h4 class="col-span-full text-sm font-semibold text-subtle">Consumption</h4>
                    @endif
                    @foreach ($stateDeltas as $delta)
                        @php
                            $deltaTitle = trim(($delta['resource'] ?? '') . ' ' . $delta['type']);
                            $deltaMetrics = array_values(array_filter([
                                data_get($delta, 'rate') !== null
                                    ? ['label' => 'Rate', 'value' => Format::number($delta['rate'], 1)] : null,
                                data_get($delta, 'minimum_fraction') !== null
                                    ? ['label' => 'Min. Fraction', 'value' => Format::valueWithUnit($delta['minimum_fraction'] * 100, '%', 1)] : null,
                                data_get($delta, 'generated_resource')
                                    ? ['label' => 'Generated Resource', 'value' => $delta['generated_resource']] : null,
                                data_get($delta, 'generated_rate') !== null
                                    ? ['label' => 'Generated Rate', 'value' => Format::number($delta['generated_rate'], 1)] : null,
                                data_get($delta, 'discharge') !== null
                                    ? ['label' => 'Discharge', 'value' => $delta['discharge'] ? 'Yes' : 'No'] : null,
                            ], static fn (?array $m): bool => $m !== null));
                        @endphp

                        <x-dl-section title="{{ $deltaTitle }}">
                            @foreach ($deltaMetrics as $metric)
                                <x-dt-dd :label="$metric['label']">{{ $metric['value'] }}</x-dt-dd>
                            @endforeach
                        </x-dl-section>
                    @endforeach

                    @if (is_array($statePowerRanges) && count($statePowerRanges) > 0)
                        <h4 class="col-span-full text-sm font-semibold text-subtle">Power States</h4>
                        @foreach ($statePowerRanges as $i => $range)
                            @php
                                $rangeTitle = match ($i) { 0 => 'Low', 1 => 'Standard', 2 => 'High', default => '' };
                                if (data_get($range, 'register_range') === 0) {
                                    $rangeTitle .= ' (Disabled)';
                                }
                                $rangeMetrics = array_values(array_filter([
                                    data_get($range, 'start') !== null
                                        ? ['label' => 'Start', 'value' => Format::valueWithUnit(data_get($range, 'start'), '', 0)] : null,
                                    data_get($range, 'modifier') !== null
                                        ? ['label' => 'Modifier', 'value' => Format::valueWithUnit(data_get($range, 'modifier'), 'x', 2)] : null,
                                ], static fn (?array $m): bool => $m !== null));
                            @endphp

                            <x-dl-section title="{{ $rangeTitle }}">
                                @foreach ($rangeMetrics as $metric)
                                    <x-dt-dd :label="$metric['label']">{{ $metric['value'] }}</x-dt-dd>
                                @endforeach
                            </x-dl-section>
                        @endforeach
                    @endif
                </x-dl-details>
                @endif
            @endforeach
        </x-dl-container>
    </div>
</div>
