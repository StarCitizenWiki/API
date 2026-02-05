@props([
    'item',
    'itemName' => null,
    'itemType' => null,
    'itemClassification' => null,
    'manufacturerName' => null,
    'gradeLetter' => null,
    'itemClass' => null,
    'itemSize' => null,
    'portsCount' => 0,
    'relatedItemsCount' => 0,
    'uexPricesCount' => 0,
    'version' => null,
])

@php
    $itemName = $itemName ?? data_get($item, 'name', 'Item');
    $itemType = $itemType ?? data_get($item, 'type');
    $itemClassification = $itemClassification ?? data_get($item, 'classification');
    $manufacturerName = $manufacturerName ?? data_get($item, 'manufacturer.name');
    $manufacturerCode = data_get($item, 'manufacturer.code');

    $itemSize = $itemSize ?? data_get($item, 'size');
    $itemClass = $itemClass ?? data_get($item, 'class');
    $grade = data_get($item, 'grade');

    $gradeLetter = $gradeLetter ?? match ($grade) {
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        default => $grade,
    };

    $classificationValue = is_string($itemClassification) ? $itemClassification : null;
    $iconName = match (true) {
        $itemType === 'PowerPlant' => 'power',
        $itemType === 'Shield' || str_contains($classificationValue ?? '', 'Shield') => 'shield',
        $itemType === 'QuantumDrive' => 'atom',
        $itemType === 'JumpDrive' => 'egg-fried',
        $itemType === 'Cooler' => 'fan',
        $itemType === 'Radar' => 'radar',
        $itemType === 'Missile' || $itemType === 'MissileLauncher' => 'rocket',
        $itemType === 'Armor' || str_contains($classificationValue ?? '', 'Armor') => 'shield-check',
        str_contains($classificationValue ?? '', 'Weapon') || str_contains($classificationValue ?? '', 'FPS') => 'crosshair',
        str_starts_with($classificationValue ?? '', 'Ship.') => 'rocket',
        default => 'box',
    };
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="flex size-14 items-center justify-center rounded-xl border border-base-300 bg-base-200 text-base-content/70">
                    <span class="sr-only">Item thumbnail</span>
                    <x-icon name="{{ $iconName }}" class="size-6" />
                </div>
                <div class="space-y-1">
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                        <h1 class="text-2xl font-semibold tracking-tight">{{ $itemName }}</h1>
                        @if ($itemType)
                            <span class="text-sm text-secondary">({{ $itemType }})</span>
                        @endif
                    </div>
                    @if ($itemClassification)
                        <div class="flex flex-wrap items-center gap-2 text-xs text-base-content/70">
                            <span class="font-semibold uppercase tracking-wide text-base-content/60">Role</span>
                            <span class="text-sm font-medium text-base-content">{{ $itemClassification }}</span>
                        </div>
                    @endif
                </div>
            </div>
            @if ($gradeLetter || $itemClass || $itemSize !== null)
                <div class="flex flex-wrap items-center gap-2 text-xs text-base-content/70">
                    @if ($gradeLetter)
                        <span class="badge badge-ghost">Grade {{ $gradeLetter }}</span>
                    @endif
                    @if ($itemClass)
                        <span class="badge badge-ghost">{{ $itemClass }}</span>
                    @endif
                    @if ($itemSize !== null)
                        <span class="badge badge-ghost">Size {{ $itemSize }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-2">
            <h2 class="card-title text-sm">Quick Facts</h2>
            <dl class="grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 tabular-nums">
                <div class="space-y-0.5">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Manufacturer</dt>
                    <dd class="text-sm font-medium">
                        @if ($manufacturerName)
                            <span class="inline-flex flex-wrap items-center gap-1">
                                <a href="{{ route('web.items.index', ['filter' => ['manufacturer' => $manufacturerName]]) }}" class="link link-primary">
                                    {{ $manufacturerName }}
                                </a>
                                @if ($manufacturerCode)
                                    <span class="badge badge-outline badge-xs">{{ $manufacturerCode }}</span>
                                @endif
                            </span>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div class="space-y-0.5">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Size</dt>
                    <dd class="text-sm font-medium">{{ $itemSize ?? '-' }}</dd>
                </div>
                <div class="space-y-0.5">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Class</dt>
                    <dd class="text-sm font-medium">{{ $itemClass ?? '-' }}</dd>
                </div>
                <div class="space-y-0.5">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Grade</dt>
                    <dd class="text-sm font-medium">{{ $gradeLetter ?? '-' }}</dd>
                </div>
                @if ($portsCount > 0)
                    <div class="space-y-0.5">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ports</dt>
                        <dd class="text-sm font-medium">{{ $portsCount }}</dd>
                    </div>
                @endif
                @if ($relatedItemsCount > 0)
                    <div class="space-y-0.5">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Related</dt>
                        <dd class="text-sm font-medium">{{ $relatedItemsCount }}</dd>
                    </div>
                @endif
                @if ($uexPricesCount > 0)
                    <div class="space-y-0.5">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">UEX Prices</dt>
                        <dd class="text-sm font-medium">{{ $uexPricesCount }}</dd>
                    </div>
                @endif
                @if ($version)
                    <div class="space-y-0.5">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Version</dt>
                        <dd class="text-sm font-medium">{{ $version }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
</div>
