@use('App\Support\Format')
@props([
    'quantumInterdictionGenerator' => null,
])

@php
    $jamming = data_get($quantumInterdictionGenerator, 'jamming', []);
    $pulse = data_get($quantumInterdictionGenerator, 'pulse', []);
    $powerFractions = data_get($quantumInterdictionGenerator, 'power_fractions', []);

    $powerFractionMetrics = [
        ['label' => 'Base Power Fraction', 'value' => data_get($powerFractions, 'base'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pulse Power Fraction', 'value' => data_get($powerFractions, 'pulse'), 'unit' => '', 'precision' => 2],
        ['label' => 'Jammer Power Fraction', 'value' => data_get($powerFractions, 'jammer'), 'unit' => '', 'precision' => 2],
    ];

    $jammingMetrics = [
        ['label' => 'Green Zone Check Range', 'value' => data_get($jamming, 'green_zone_check_range'), 'unit' => 'm', 'precision' => 0],
    ];

    $pulseTimingMetrics = [
        ['label' => 'Activation Phase Duration', 'value' => data_get($pulse, 'activation_phase_duration'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Disperse Charge Time', 'value' => data_get($pulse, 'disperse_charge_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Decrease Charge Rate Time', 'value' => data_get($pulse, 'decrease_charge_rate_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Increase Charge Rate Time', 'value' => data_get($pulse, 'increase_charge_rate_time'), 'unit' => 's', 'precision' => 2],
    ];

    $maxPowerDrawMetrics = [
        ['label' => 'Jamming Max Power Draw', 'value' => data_get($jamming, 'max_power_draw'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pulse Max Power Draw', 'value' => data_get($pulse, 'max_power_draw'), 'unit' => '', 'precision' => 2],
    ];

    $advancedMetrics = [
        ['label' => 'Stop Charging Power Fraction', 'value' => data_get($pulse, 'stop_charging_power_fraction'), 'unit' => '', 'precision' => 2],
        ['label' => 'Max Charge Rate Power Fraction', 'value' => data_get($pulse, 'max_charge_rate_power_fraction'), 'unit' => '', 'precision' => 2],
        ['label' => 'Active Power Fraction', 'value' => data_get($pulse, 'active_power_fraction'), 'unit' => '', 'precision' => 2],
        ['label' => 'Tethering Power Fraction', 'value' => data_get($pulse, 'tethering_power_fraction'), 'unit' => '', 'precision' => 2],
        ['label' => 'Pulse Green Zone Check Range', 'value' => data_get($pulse, 'green_zone_check_range'), 'unit' => 'm', 'precision' => 0],
    ];

    $jammingRange = data_get($jamming, 'range');
    $pulseRadius = data_get($pulse, 'radius');
    $chargeTime = data_get($pulse, 'charge_time');
    $dischargeTime = data_get($pulse, 'discharge_time');
    $cooldownTime = data_get($pulse, 'cooldown_time');

@endphp

<x-item-card title="Quantum Interdiction Generator">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Jamming Range" :value="$jammingRange">{{ Format::valueWithUnit($jammingRange, 'm', 0) }}</x-dt-dd>
            <x-dt-dd label="Pulse Radius" :value="$pulseRadius">{{ Format::valueWithUnit($pulseRadius, 'm', 0) }}</x-dt-dd>
            <x-dt-dd label="Charge Time" :value="$chargeTime">{{ Format::valueWithUnit($chargeTime, 's', 1) }}</x-dt-dd>
            <x-dt-dd label="Discharge Time" :value="$dischargeTime">{{ Format::valueWithUnit($dischargeTime, 's', 1) }}</x-dt-dd>
            <x-dt-dd label="Cooldown Time" :value="$cooldownTime">{{ Format::valueWithUnit($cooldownTime, 's', 1) }}</x-dt-dd>
        </x-slot:head>

        <x-dl-section title="Power Fractions">
            @foreach ($powerFractionMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Jamming">
            @foreach ($jammingMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Pulse Timing">
            @foreach ($pulseTimingMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Max Power Draw">
            @foreach ($maxPowerDrawMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>

    <x-dl-details title="Advanced Power & Range Details">
        <x-dl-section>
            @foreach ($advancedMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-details>
</x-item-card>
