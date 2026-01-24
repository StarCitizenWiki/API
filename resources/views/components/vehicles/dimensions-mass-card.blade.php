@props(['vehicle'])

@php
    $dimension = data_get($vehicle, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');

    $crossSection = data_get($vehicle, 'cross_section', []);

    $massTotal = data_get($vehicle, 'mass_total', data_get($vehicle, 'mass'));
    $massHull = data_get($vehicle, 'mass_hull');
    $massLoadout = data_get($vehicle, 'mass_loadout');
@endphp

<details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
    <summary class="collapse-title min-h-11 py-3 font-semibold">
        Dimensions & Mass
    </summary>
    <div class="collapse-content">
        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Length</dt>
                <dd class="text-sm font-medium">{{ $length ? number_format($length, 1) . ' m' : '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Width</dt>
                <dd class="text-sm font-medium">{{ $width ? number_format($width, 1) . ' m' : '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Height</dt>
                <dd class="text-sm font-medium">{{ $height ? number_format($height, 1) . ' m' : '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section</dt>
                <dd class="text-sm font-medium">
                    @if (data_get($crossSection, 'length') || data_get($crossSection, 'width') || data_get($crossSection, 'height'))
                        {{ fmt_or_dash(data_get($crossSection, 'length')) }}
                        × {{ fmt_or_dash(data_get($crossSection, 'width')) }}
                        × {{ fmt_or_dash(data_get($crossSection, 'height')) }}
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass Total</dt>
                <dd class="text-sm font-medium">{{ $massTotal ? fmt_value_with_unit($massTotal, 'kg', 0) : '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass Hull</dt>
                <dd class="text-sm font-medium">{{ $massHull ? fmt_value_with_unit($massHull, 'kg', 0) : '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass Loadout</dt>
                <dd class="text-sm font-medium">{{ $massLoadout ? fmt_value_with_unit($massLoadout, 'kg', 0) : '-' }}</dd>
            </div>
        </dl>
    </div>
</details>
