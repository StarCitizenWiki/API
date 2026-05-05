@use('App\Support\Format')
@props([
    'emission',
])

@php
    $ir = data_get($emission, 'ir');
    $emMin = data_get($emission, 'em_min');
    $emMax = data_get($emission, 'em_max');
    $emDecay = data_get($emission, 'em_decay');
    $emPerSegment = data_get($emission, 'em_per_segment');

    $irMetrics = array_values(array_filter([
        ['label' => 'Emission', 'value' => Format::numberOrDash($ir, 1)],
    ], static fn (?array $m): bool => $m !== null));

    $emMetrics = array_values(array_filter([
        ['label' => 'Emission', 'value' => Format::range($emMin, $emMax, '', 1)],
        ['label' => 'Decay', 'value' => Format::numberOrDash($emDecay, 2)],
        $emPerSegment !== null ? ['label' => 'Per Segment', 'value' => Format::numberOrDash($emPerSegment, 0)] : null,
    ], static fn (?array $m): bool => $m !== null));

    $hasMetrics = $irMetrics !== [] || $emMetrics !== [];
@endphp

@if ($hasMetrics)
<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Emission</h2>

        <div class="grid gap-8 grid-cols-1 sm:grid-cols-2">
            @if ($irMetrics !== [])
                <x-dl-section title="IR">
                    @foreach ($irMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">{{ $metric['value'] }}</x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif
            @if ($emMetrics !== [])
                <x-dl-section title="EM">
                    @foreach ($emMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">{{ $metric['value'] }}</x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif
        </div>
    </div>
</div>
@endif

