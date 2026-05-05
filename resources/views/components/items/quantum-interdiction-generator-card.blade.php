@use('App\Support\Format')
@props([
    'quantumInterdictionGenerator' => null,
])

@php
    $jamming = data_get($quantumInterdictionGenerator, 'jamming', []);
    $pulse = data_get($quantumInterdictionGenerator, 'pulse', []);
    $powerFractions = data_get($quantumInterdictionGenerator, 'power_fractions', []);

    $powerFractionMetrics = array_values(array_filter([
        ['label' => 'Base Power Fraction', 'value' => data_get($powerFractions, 'base'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pulse Power Fraction', 'value' => data_get($powerFractions, 'pulse'), 'unit' => '', 'precision' => 2],
        ['label' => 'Jammer Power Fraction', 'value' => data_get($powerFractions, 'jammer'), 'unit' => '', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));

    $jammingMetrics = array_values(array_filter([
        ['label' => 'Green Zone Check Range', 'value' => data_get($jamming, 'green_zone_check_range'), 'unit' => 'm', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));

    $pulseTimingMetrics = array_values(array_filter([
        ['label' => 'Activation Phase Duration', 'value' => data_get($pulse, 'activation_phase_duration'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Disperse Charge Time', 'value' => data_get($pulse, 'disperse_charge_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Decrease Charge Rate Time', 'value' => data_get($pulse, 'decrease_charge_rate_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Increase Charge Rate Time', 'value' => data_get($pulse, 'increase_charge_rate_time'), 'unit' => 's', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));

    $maxPowerDrawMetrics = array_values(array_filter([
        ['label' => 'Jamming Max Power Draw', 'value' => data_get($jamming, 'max_power_draw'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pulse Max Power Draw', 'value' => data_get($pulse, 'max_power_draw'), 'unit' => '', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));

    $advancedMetrics = array_values(array_filter([
        ['label' => 'Stop Charging Power Fraction', 'value' => data_get($pulse, 'stop_charging_power_fraction'), 'unit' => '', 'precision' => 2],
        ['label' => 'Max Charge Rate Power Fraction', 'value' => data_get($pulse, 'max_charge_rate_power_fraction'), 'unit' => '', 'precision' => 2],
        ['label' => 'Active Power Fraction', 'value' => data_get($pulse, 'active_power_fraction'), 'unit' => '', 'precision' => 2],
        ['label' => 'Tethering Power Fraction', 'value' => data_get($pulse, 'tethering_power_fraction'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pulse Green Zone Check Range', 'value' => data_get($pulse, 'green_zone_check_range'), 'unit' => 'm', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Quantum Interdiction Generator</h2>

        <x-dl-container>
            <x-slot:head>
                <x-dt-dd label="Jamming Range">{{ Format::valueWithUnit(data_get($jamming, 'range'), 'm', 0) }}</x-dt-dd>
                <x-dt-dd label="Pulse Radius">{{ Format::valueWithUnit(data_get($pulse, 'radius'), 'm', 0) }}</x-dt-dd>
                <x-dt-dd label="Charge Time">{{ Format::valueWithUnit(data_get($pulse, 'charge_time'), 's', 1) }}</x-dt-dd>
                <x-dt-dd label="Discharge Time">{{ Format::valueWithUnit(data_get($pulse, 'discharge_time'), 's', 1) }}</x-dt-dd>
                <x-dt-dd label="Cooldown Time">{{ Format::valueWithUnit(data_get($pulse, 'cooldown_time'), 's', 1) }}</x-dt-dd>
            </x-slot:head>

            <x-dl-section title="Power Fractions">
                @foreach ($powerFractionMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Jamming">
                @foreach ($jammingMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Pulse Timing">
                @foreach ($pulseTimingMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Max Power Draw">
                @foreach ($maxPowerDrawMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        </x-dl-container>

        @if (count($advancedMetrics) > 0)
            <x-dl-details title="Advanced Power & Range Details">
                <x-dl-section>
                    @foreach ($advancedMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            </x-dl-details>
        @endif
    </div>
</div>
