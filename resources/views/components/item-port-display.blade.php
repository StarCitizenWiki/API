@php use Illuminate\Support\Str; @endphp
@props([
    'port',
])

@php
    $portName = data_get($port, 'name');
    $label = Str::headline($portName ?? 'Port');

    $isEditable = (bool) data_get($port, 'editable');
    $isLocked = ! $isEditable;

    $size = data_get($port, 'size');
    $sizeMin = data_get($port, 'sizes.min');
    $sizeMax = data_get($port, 'sizes.max');
    $sizeLabel = $sizeMin === $sizeMax ? "S{$sizeMin}" : "S{$sizeMin}-S{$sizeMax}";

    $equippedItem = data_get($port, 'equipped_item', data_get($port, 'equipped_port_item'));
    $hasEquipped = ! empty($equippedItem) && filled(data_get($equippedItem, 'name'));
    $equippedName = $hasEquipped ? data_get($equippedItem, 'name') : null;
    $equippedUrl = $hasEquipped ? data_get($equippedItem, 'web_url') : null;

    $compatibleTypes = collect(data_get($port, 'compatible_types', []) ?? []);
    $typeAnnotation = $compatibleTypes
        ->flatMap(fn (array $ct) => collect(data_get($ct, 'sub_types', []))
            ->map(fn (string $st) => Str::headline($st)))
        ->implode(', ');

    $isPlaceholder = $size === 0 && $compatibleTypes->isEmpty();

    // Browse config
    $browseType = $compatibleTypes->isNotEmpty() ? data_get($compatibleTypes->first(), 'type') : null;
    $browseSubType = $compatibleTypes->isNotEmpty()
        ? data_get($compatibleTypes->first(), 'sub_types.0')
        : null;
    $canBrowse = $browseType !== null && ! $isLocked;

    $browseConfig = [];
    $browseFilters = [];

    if ($canBrowse) {
        $browseConfig = [
            'type' => $browseType,
            'subType' => $browseSubType,
            'sizeMin' => $sizeMin,
            'sizeMax' => $sizeMax,
            'portTags' => data_get($port, 'required_tags'),
        ];

        $browseFilters = array_filter([
            'type' => $browseType,
            'sub_type' => $browseSubType,
        ]);

        if ($sizeMin !== null && $sizeMax !== null) {
            $browseFilters['size'] = implode(',', range($sizeMin, $sizeMax));
        }

        $requiredTags = data_get($port, 'required_tags', []);
        if (! empty($requiredTags)) {
            $tags = is_array($requiredTags) ? $requiredTags : [$requiredTags];
            $browseFilters['port_tags'] = count($tags) === 1 ? $tags[0] : $tags;
        }
    }

    $browseUrl = $canBrowse
        ? route('web.items.index', ['filter' => $browseFilters])
        : null;
@endphp

@if (! $isPlaceholder)
    <div
        @if ($canBrowse)
            x-data="portEquippable(@js($browseConfig))"
            @click.stop="toggle($el)"
        @endif
        @class([
            'grid grid-cols-[auto_minmax(0,1fr)] items-stretch overflow-hidden rounded-lg border-2 bg-base-200 border-base-200',
            'cursor-pointer hover:bg-base-300/50 transition-colors' => $canBrowse,
        ])
        data-testid="port-display-details"
    >
        <aside class="grid grid-flow-col auto-cols-max items-stretch divide-x divide-base-200 bg-base-100">
            <div class="flex items-center gap-1 px-2 py-2 font-medium">
                @if ($isLocked)
                    <x-icon name="lock" class="size-3 shrink-0 text-muted" />
                @endif

                @if ($size !== null && $size > 0)
                    <span class="text-md" title="Equippable Size">{{ $sizeLabel }}</span>
                @endif
            </div>
        </aside>

        <div class="flex min-w-0 flex-col justify-center px-2 py-2">
            <span class="truncate text-sm">
                @if ($hasEquipped && $equippedUrl)
                    <a href="{{ $equippedUrl }}" class="link-primary" @click.stop>{{ $label }}</a>
                @else
                    {{ $label }}
                @endif

                @if ($typeAnnotation)
                    <span class="text-xs font-normal text-subtle">({{ $typeAnnotation }})</span>
                @endif
            </span>

            @if ($hasEquipped)
                <span class="truncate text-xs text-subtle">{{ $equippedName }}</span>
            @else
                <span class="truncate text-xs text-subtle italic">Empty</span>
            @endif
        </div>

        @if ($canBrowse && $browseUrl)
            <x-port-browse-popup :type="$browseType" :browse-url="$browseUrl" />
        @endif
    </div>
@endif
