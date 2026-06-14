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
            'distance' => $gm . ' Gm',
            'time' => $time !== null ? sprintf('%d:%02d', (int) floor($time / 60), (int) round($time % 60)) : '-',
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

<div x-data="quantumDriveCalc(@js($calcConfig))" class="card card-border bg-base-200">
    <div class="flex items-center gap-2 mb-3">
        <template x-if="!error">
            <div class="flex items-center gap-2 w-full">
                <select
                    x-model="startUuid"
                    @change="if (startEntity && endEntity && startEntity.system !== endEntity.system) endUuid = null"
                    class="select select-sm flex-1 min-w-0 border-1"
                    :disabled="loading"
                >
                    <option value="" disabled selected x-text="loading ? 'Loading...' : 'From'"></option>
                    <template x-for="sys in systems" :key="sys">
                        <optgroup :label="sys.charAt(0).toUpperCase() + sys.slice(1)">
                            <template x-for="e in entities.filter(e => e.system === sys)" :key="e.uuid">
                                <option :value="e.uuid" x-text="e.name"></option>
                            </template>
                        </optgroup>
                    </template>
                </select>

                <x-icon name="arrow-right" size="sm" class="shrink-0 opacity-40"/>

                <select
                    x-model="endUuid"
                    class="select select-sm flex-1 min-w-0 border-1"
                    :disabled="loading"
                >
                    <option value=""  selected>Select a destination</option>
                    <template x-for="sys in systems" :key="sys">
                        <optgroup :label="sys.charAt(0).toUpperCase() + sys.slice(1)">
                            <template x-for="e in endOptions.filter(e => e.system === sys)" :key="e.uuid">
                                <option :value="e.uuid" x-text="e.name"></option>
                            </template>
                        </optgroup>
                    </template>
                </select>

                @if($fuelCapacity)
                    <div class="flex items-center gap-2 shrink-0" title="Tank fill level">
                        <button
                            type="button"
                            @click="showTank = !showTank"
                            class="btn btn-xs btn-ghost gap-1 shadow-none border-1"
                            :class="tankFill < 100 && 'btn-warning'"
                        >
                            <x-icon name="flame" size="sm"/>
                            <span class="tabular-nums" x-text="tankFill + '%'"></span>
                        </button>
                        <div x-show="showTank" x-transition class="flex items-center gap-2">
                            <input
                                type="range"
                                min="1"
                                max="100"
                                x-model.number="tankFill"
                                class="range range-xs range-primary w-20"
                            >
                        </div>
                    </div>
                @endif
            </div>
        </template>
        <template x-if="error">
            <span class="text-sm text-center text-error">Failed to load position data.</span>
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
            <template x-if="distanceFromSelection !== null && !sameEntity">
                <tr class="bg-base-300">
                    <td class="text-subtle border-base-content border-b-1">
                        <span x-text="startEntity?.name + ' to ' + endEntity?.name"></span>
                        <span class="text-muted" x-text="'(' + distanceFromSelection.toFixed(2) + ' Gm)'"></span>
                    </td>
                    <td class="text-right font-semibold tabular-nums border-base-content border-b-1" x-text="selectionTimeFormatted"></td>
                    <td class="text-right font-semibold tabular-nums border-base-content border-b-1" x-text="selectionFuelFormatted"></td>
                    @if($fuelCapacity)
                        <td class="text-right font-semibold tabular-nums border-base-content border-b-1" :class="selectionExceedsTank ? 'text-warning' : 'text-subtle'" x-text="selectionTankFormatted"></td>
                    @endif
                </tr>
            </template>

            @foreach($precomputed as $route)
                <tr>
                    <td class="text-subtle">{{ $route['label'] }} <span class="text-muted">({{ $route['distance'] }})</span></td>
                    <td class="text-right font-semibold tabular-nums">{{ $route['time'] }}</td>
                    <td class="text-right font-semibold tabular-nums">{{ $route['fuel'] }}</td>
                    @if($fuelCapacity)
                        <td class="text-right font-semibold tabular-nums @if($route['exceeds']) text-warning @else text-subtle @endif">{{ $route['tank'] }}</td>
                    @endif
                </tr>
            @endforeach

            <tr>
                <td>
                    <div class="join">
                        <input
                            type="number"
                            min="0"
                            step="1"
                            placeholder="0"
                            x-model.number="distanceInput"
                            class="input input-xs border-1 pl-2 pr-0 join-item w-16 tabular-nums"
                        >
                        <span class="join-item bg-base-200 text-xs text-muted px-2 flex items-center">Gm</span>
                    </div>
                </td>
                <td class="text-right font-semibold tabular-nums" x-text="travelTimeFormatted"></td>
                <td class="text-right font-semibold tabular-nums" x-text="fuelFormatted"></td>
                @if($fuelCapacity)
                    <td class="text-right font-semibold tabular-nums" :class="exceedsTank ? 'text-warning' : 'text-subtle'" x-text="tankFormatted"></td>
                @endif
            </tr>
        </tbody>
    </table>
</div>
