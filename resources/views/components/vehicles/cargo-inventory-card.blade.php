@use('App\Support\Format')
@props(['vehicle'])

@php
    $cargoGrids = data_get($vehicle, 'cargo_grids', []);
    $cargoLimits = data_get($vehicle, 'cargo_limits');
    $minScuBox = data_get($cargoLimits, 'min_scu_box');
    $maxScuBox = data_get($cargoLimits, 'max_scu_box');
    $oreCapacity = data_get($vehicle, 'ore_capacity');
@endphp

@if ((is_array($cargoGrids) && $cargoGrids !== []) || $oreCapacity !== null)
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">
                Cargo Grids
                <span class="badge badge-soft text-xs">{{ count($cargoGrids) }}</span>
            </h2>

            @if ($oreCapacity !== null)
                <div class="mt-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Ore Capacity</span>
                        <span class="badge badge-soft badge-sm">{{ Format::valueWithUnit($oreCapacity, 'SCU', 0) }}</span>
                    </div>
                </div>
            @endif

            @if (is_array($cargoGrids) && $cargoGrids !== [])
            <div class="overflow-x-auto overflow-y-auto max-h-48">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Capacity</th>
                        <th>Dimensions</th>
                        <th>Box Size</th>
                        <th>Type</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($cargoGrids as $grid)
                        @php
                            $gridMinScuBox = data_get($grid, 'min_scu_box');
                            $gridMaxScuBox = data_get($grid, 'max_scu_box');
                        @endphp
                        <tr>
                            <td>{{ Format::valueWithUnit($grid['scu'], 'SCU', 0) }}</td>
                            <td>
                                {{ Format::valueWithUnit(data_get($grid, 'width'), 'm', 1) }} × {{ Format::valueWithUnit(data_get($grid, 'height'), 'm', 1) }} × {{ Format::valueWithUnit(data_get($grid, 'length'), 'm', 1) }}
                            </td>
                            <td>@if ($gridMinScuBox !== null && $gridMaxScuBox !== null && $gridMinScuBox !== $gridMaxScuBox) {{ Format::valueWithUnit($gridMinScuBox, 'SCU', 0) }} - {{ Format::valueWithUnit($gridMaxScuBox, 'SCU', 0) }} @elseif ($gridMaxScuBox !== null) {{ Format::valueWithUnit($gridMaxScuBox, 'SCU', 0) }} @elseif ($gridMinScuBox !== null) {{ Format::valueWithUnit($gridMinScuBox, 'SCU', 0) }} @else - @endif</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @if (data_get($grid, 'open') === true)
                                        Open
                                    @endif
                                    @if (data_get($grid, 'external') === true)
                                        External
                                    @endif
                                    @if (data_get($grid, 'closed') === true)
                                        Closed
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </section>
@endif
