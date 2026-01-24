@props(['vehicle'])

@php
    $cargoGrids = data_get($vehicle, 'cargo_grids', []);
@endphp

@unless(empty($cargoGrids))

<details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
    <summary class="collapse-title min-h-11 py-3 font-semibold">
        <span class="flex items-center gap-2">
            <span>Cargo Grids</span>
            @if (count($cargoGrids) > 0)
                <span class="badge badge-ghost text-xs">{{ count($cargoGrids) }}</span>
            @endif
        </span>
    </summary>
    <div class="collapse-content">
    @if (is_array($cargoGrids) && $cargoGrids !== [])
        <div class="overflow-x-auto">
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
    @else
        <p class="text-sm text-base-content/60">No cargo grid data available</p>
    @endif
    </div>
</details>
@endunless
