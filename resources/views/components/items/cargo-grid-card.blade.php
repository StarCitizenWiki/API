@props([
    'cargoGrid',
])

@php
    $className = data_get($cargoGrid, 'class_name');
    $scu = data_get($cargoGrid, 'scu');
    $capacity = data_get($cargoGrid, 'capacity');
    $capacityName = data_get($cargoGrid, 'capacity_name');
    $isOpen = data_get($cargoGrid, 'is_open');
    $isExternal = data_get($cargoGrid, 'is_external');
    $isClosed = data_get($cargoGrid, 'is_closed');
    $x = data_get($cargoGrid, 'x');
    $y = data_get($cargoGrid, 'y');
    $z = data_get($cargoGrid, 'z');
    $minSize = data_get($cargoGrid, 'min_size', []);
    $maxSize = data_get($cargoGrid, 'max_size', []);

    $hasPosition = $x !== null || $y !== null || $z !== null;
    $hasMinSize = is_array($minSize) && array_filter($minSize, fn($v) => $v !== null);
    $hasMaxSize = is_array($maxSize) && array_filter($maxSize, fn($v) => $v !== null);
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="box" class="size-4 text-primary" />
            <span>Cargo Grid Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($className !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class Name</dt>
                    <dd class="text-sm font-medium">{{ $className }}</dd>
                </div>
            @endif
            @if ($scu !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">SCU</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$scu, 2) }}</dd>
                </div>
            @endif
            @if ($capacity !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                    <dd class="text-sm font-medium">
                        {{ number_format((float)$capacity, 2) }}
                        @if ($capacityName)
                            {{ $capacityName }}
                        @endif
                    </dd>
                </div>
            @endif
        </dl>

        @if (array_key_exists('is_open', $cargoGrid) || array_key_exists('is_external', $cargoGrid) || array_key_exists('is_closed', $cargoGrid))
            <div class="flex flex-wrap gap-2">
                @if (array_key_exists('is_open', $cargoGrid))
                    <span class="badge badge-outline text-sm text-nowrap">Open: {{ $isOpen ? 'Yes' : 'No' }}</span>
                @endif
                @if (array_key_exists('is_external', $cargoGrid))
                    <span class="badge badge-outline text-sm text-nowrap">External: {{ $isExternal ? 'Yes' : 'No' }}</span>
                @endif
                @if (array_key_exists('is_closed', $cargoGrid))
                    <span class="badge badge-outline text-sm text-nowrap">Closed: {{ $isClosed ? 'Yes' : 'No' }}</span>
                @endif
            </div>
        @endif

        @if ($hasPosition)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Position</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($x !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">X</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$x, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($y !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Y</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$y, 2) }} m</dd>
                            </div>
                        @endif
                        @if ($z !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Z</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$z, 2) }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasMinSize)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Min Size</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($minSize, 'x') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">X</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($minSize, 'x'), 2) }} m</dd>
                            </div>
                        @endif
                        @if (data_get($minSize, 'y') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Y</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($minSize, 'y'), 2) }} m</dd>
                            </div>
                        @endif
                        @if (data_get($minSize, 'z') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Z</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($minSize, 'z'), 2) }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasMaxSize)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Max Size</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($maxSize, 'x') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">X</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($maxSize, 'x'), 2) }} m</dd>
                            </div>
                        @endif
                        @if (data_get($maxSize, 'y') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Y</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($maxSize, 'y'), 2) }} m</dd>
                            </div>
                        @endif
                        @if (data_get($maxSize, 'z') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Z</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($maxSize, 'z'), 2) }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
