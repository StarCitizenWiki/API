@use('App\Support\Format')
@props([
    'quantumDrive',
    'fuelCapacity' => null,
])

@php
    $travelTime10gm = data_get($quantumDrive, 'travel_time_10gm.seconds');
    $fuelConsumption = data_get($quantumDrive, 'fuel_consumption_scu_per_gm');

    $routes = [
        ['label' => 'Hurston to Crusader', 'distance' => 32],
        ['label' => 'Crusader to microTech', 'distance' => 57],
        ['label' => 'Terminus to Pyro V', 'distance' => 101],
    ];

    $precomputed = [];
    foreach ($routes as $route) {
        $gm = $route['distance'];
        $time = $travelTime10gm ? ($gm / 10) * $travelTime10gm : null;
        $fuel = $fuelConsumption ? $gm * $fuelConsumption : null;

        $precomputed[] = [
            'label' => $route['label'],
            'distance' => $gm,
            'time' => $time !== null ? Format::number(floor($time / 60), 0) . 'min ' . round($time % 60) . 's' : '-',
            'fuel' => $fuel !== null ? number_format($fuel, 2) . ' SCU' : '-',
            'tank' => ($fuel !== null && $fuelCapacity > 0) ? round(($fuel / $fuelCapacity) * 100) . '%' : null,
            'exceeds' => $fuel !== null && $fuelCapacity > 0 && ($fuel / $fuelCapacity) > 1,
        ];
    }

    $calcConfig = [
        'travelTime10gm' => $travelTime10gm,
        'fuelConsumption' => $fuelConsumption,
        'fuelCapacity' => $fuelCapacity,
    ];
@endphp

<div>
    <div x-data="quantumDriveCalc(@js($calcConfig))" class="flex items-center gap-2 flex-wrap mb-2">
        <div class="flex items-center gap-1">
            <input
                type="number"
                min="0"
                step="1"
                placeholder="0"
                x-model.number="distanceInput"
                class="input input-sm input-bordered w-20 text-sm tabular-nums"
            >
            <span class="text-xs text-muted">GM</span>
        </div>

        <template x-if="distance > 0">
            <div class="flex items-center gap-2 text-xs">
                <span class="text-subtle">
                    <x-icon name="clock" size="sm" class="inline opacity-60"/>
                    <strong class="font-semibold tabular-nums" x-text="travelTimeFormatted"></strong>
                </span>
                <span class="text-subtle">
                    <x-icon name="flame" size="sm" class="inline opacity-60"/>
                    <strong class="font-semibold tabular-nums" x-text="fuelFormatted"></strong>
                </span>
                <template x-if="tankFormatted !== null">
                    <span :class="exceedsTank ? 'text-warning' : 'text-subtle'">
                        (<strong class="font-semibold tabular-nums" x-text="tankFormatted"></strong> tank)
                    </span>
                </template>
            </div>
        </template>
    </div>

    <table class="table table-xs">
        <thead>
            <tr>
                <th>Route</th>
                <th class="text-right">Time</th>
                <th class="text-right">Fuel</th>
                @if($fuelCapacity)
                    <th class="text-right">Tank</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($precomputed as $route)
                <tr>
                    <td class="text-subtle">{{ $route['label'] }} <span class="text-muted">({{ $route['distance'] }} GM)</span></td>
                    <td class="text-right font-semibold tabular-nums">{{ $route['time'] }}</td>
                    <td class="text-right font-semibold tabular-nums">{{ $route['fuel'] }}</td>
                    @if($fuelCapacity)
                        <td class="text-right font-semibold tabular-nums @if($route['exceeds']) text-warning @else text-subtle @endif">{{ $route['tank'] }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
