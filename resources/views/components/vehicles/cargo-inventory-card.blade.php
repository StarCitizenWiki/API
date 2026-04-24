@props(['vehicle'])

@php
    $cargoGrids = data_get($vehicle, 'cargo_grids', []);
@endphp

@if (is_array($cargoGrids) && $cargoGrids !== [])
    <section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">
                Cargo Grids
                <span class="badge badge-ghost text-xs">{{ count($cargoGrids) }}</span>
            </h2>

            <div class="overflow-x-auto overflow-y-auto max-h-48">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Capacity</th>
                        <th>Dimensions</th>
                        <th>Type</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($cargoGrids as $grid)
                        <tr>
                            <td>{{ fmt_value_with_unit($grid['scu'], 'SCU', 0) }}</td>
                            <td>
                                {{ fmt_value_with_unit(data_get($grid, 'width'), 'm', 1) }} × {{ fmt_value_with_unit(data_get($grid, 'height'), 'm', 1) }} × {{ fmt_value_with_unit(data_get($grid, 'length'), 'm', 1) }}
                            </td>
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
        </div>
    </section>
@endif
