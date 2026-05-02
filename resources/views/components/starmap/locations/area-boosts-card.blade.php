@props(['areas'])

@php
    $areas = is_array($areas) ? $areas : [];
@endphp

@if ($areas !== [])
    <div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow-sm']) }} data-testid="starmap-location-area-boosts">
        <div class="card-body gap-3 p-5">
            <h3 class="text-base font-semibold tracking-tight">Area Boosts</h3>
            <p class="text-sm text-subtle">Areas with spawn rate multipliers affecting all resources in this zone.</p>

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>Modifier</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($areas as $area)
                        @php
                            $areaName = data_get($area, 'name');
                            $globalMod = data_get($area, 'global_modifier');
                        @endphp

                        <tr class="hover">
                            <td class="text-sm font-medium">{{ data_get($area, 'name') }}</td>
                            <td class="text-sm tabular-nums">
                                <span class="badge badge-success badge-sm">×{{ data_get($area, 'global_modifier') }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
