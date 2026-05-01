@props([
    'inventory',
 ])

@php
    $scuConverted = data_get($inventory, 'scu_converted');
    $isOpen = data_get($inventory, 'open');
    $isExternal = data_get($inventory, 'external');
    $isClosed = data_get($inventory, 'closed');
    $width = data_get($inventory, 'width');
    $height = data_get($inventory, 'height');
    $length = data_get($inventory, 'length');

    $hasDimensions = $width !== null && $height !== null && $length !== null;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Inventory</h2>


        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($scuConverted !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Capacity</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($scuConverted, data_get($inventory, 'unit', 'SCU'), 1) }}</dd>
                </div>
            @endif
            @if ($hasDimensions)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Dimensions</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($width, 'm', 1) }} × {{ fmt_value_with_unit($height, 'm', 1) }} × {{ fmt_value_with_unit($length, 'm', 1) }}</dd>
                </div>
            @endif
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Type</dt>
                <dd class="text-sm font-semibold text-base-content">
                    {{ $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-')) }}
                </dd>
            </div>
        </dl>
    </div>
</div>
