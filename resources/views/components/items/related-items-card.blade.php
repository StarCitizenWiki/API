@props([
    'setItems' => [],
    'setName' => null,
    'bySize' => [],
    'byGrade' => [],
    'byColor' => [],
    'currentItemUuid',
    'currentItem' => [],
])

@php
    $setItems = is_array($setItems) ? $setItems : [];
    $bySize = is_array($bySize) ? $bySize : [];
    $byGrade = is_array($byGrade) ? $byGrade : [];
    $byColor = is_array($byColor) ? $byColor : [];

    $setItemRows = array_map(static fn (array $item): array => [
        'label' => $item['name'] ?? '-',
        'meta' => $item['sub_type'] ?? '-',
        'href' => $item['web_url'] ?? null,
        'current' => ($item['uuid'] ?? null) === $currentItemUuid,
    ], $setItems);

    // The current item
    $makeRow = static fn (array $peer, string $meta): array => [
        'uuid' => $peer['uuid'] ?? null,
        'label' => $peer['name'] ?? '-',
        'meta' => $meta,
        'href' => $peer['web_url'] ?? null,
        'current' => ($peer['uuid'] ?? null) === $currentItemUuid,
        'size' => $peer['size'] ?? null,
        'gradeLabel' => (string) ($peer['grade_label'] ?? ''),
    ];

    $sizeRows = array_map(
        static fn (array $peer): array => $makeRow($peer, 'Size '.($peer['size'] ?? '-')),
        $bySize,
    );

    $gradeRows = array_map(
        static fn (array $peer): array => $makeRow($peer, (string) ($peer['grade_label'] ?? '-')),
        $byGrade,
    );

    if ($sizeRows !== [] && ! in_array($currentItemUuid, array_column($sizeRows, 'uuid'), true)) {
        $sizeRows[] = [
            'uuid' => $currentItemUuid,
            'label' => data_get($currentItem, 'name', '-'),
            'meta' => 'Size '.(data_get($currentItem, 'size') ?? '-'),
            'href' => null,
            'current' => true,
            'size' => data_get($currentItem, 'size'),
            'gradeLabel' => '',
        ];

        usort($sizeRows, static fn (array $a, array $b): int => ($a['size'] ?? PHP_INT_MAX) <=> ($b['size'] ?? PHP_INT_MAX));
    }

    if ($gradeRows !== [] && ! in_array($currentItemUuid, array_column($gradeRows, 'uuid'), true)) {
        $gradeRows[] = [
            'uuid' => $currentItemUuid,
            'label' => data_get($currentItem, 'name', '-'),
            'meta' => (string) (data_get($currentItem, 'grade') ?? '-'),
            'href' => null,
            'current' => true,
            'size' => null,
            'gradeLabel' => (string) (data_get($currentItem, 'grade') ?? ''),
        ];

        usort($gradeRows, static fn (array $a, array $b): int => strcmp($a['gradeLabel'], $b['gradeLabel']));
    }

    $colorChips = array_map(static fn (array $peer): array => [
        'label' => $peer['variant_name'] ?? $peer['name'] ?? 'Base',
        'href' => $peer['web_url'] ?? null,
        'current' => ($peer['uuid'] ?? null) === $currentItemUuid,
    ], $byColor);

    $axes = [];

    if ($sizeRows !== []) {
        $axes[] = ['key' => 'sizes', 'title' => 'Sizes', 'rows' => $sizeRows, 'chips' => null];
    }

    if ($gradeRows !== []) {
        $axes[] = ['key' => 'grades', 'title' => 'Grades', 'rows' => $gradeRows, 'chips' => null];
    }

    if ($colorChips !== []) {
        $axes[] = ['key' => 'colors', 'title' => 'Colors', 'rows' => null, 'chips' => $colorChips];
    }

    $axisCount = static fn (array $axis): int => $axis['chips'] !== null ? count($axis['chips']) : count($axis['rows']);
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow', 'data-testid' => 'item-related-items-card']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <h2 class="card-title text-base">Related Items</h2>

        @if ($setItemRows !== [])
            <div class="space-y-2">
                <div class="flex items-center gap-1.5">
                    <h3 class="font-semibold text-sm">Set Items</h3>
                    <span class="badge badge-ghost badge-sm text-xs">{{ count($setItemRows) }}</span>
                    @if ($setName)
                        <span class="text-xs text-subtle truncate">- {{ $setName }}</span>
                    @endif
                </div>

                <ul>
                    @foreach ($setItemRows as $row)
                        @if (! $row['current'] && filled($row['href']))
                            <li class="border-b border-base-200 last:border-b-0">
                                <a href="{{ $row['href'] }}" class="flex items-center gap-3 px-3 py-2.5 hover:bg-base-200 transition-colors">
                                    <span class="text-sm font-medium truncate link link-primary">{{ $row['label'] }}</span>
                                    <span class="text-xs text-subtle shrink-0 ms-auto">{{ $row['meta'] }}</span>
                                    <x-icon name="chevron-right" class="size-4 text-muted shrink-0" />
                                </a>
                            </li>
                        @else
                            <li class="flex items-center gap-3 px-3 py-2.5 border-b border-base-200 last:border-b-0">
                                <span class="text-sm font-medium opacity-60 truncate">{{ $row['label'] }}</span>
                                <span class="text-xs text-subtle shrink-0 ms-auto">{{ $row['meta'] }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        @if (count($axes) === 1)
            <div class="space-y-2" data-testid="item-related-axis-{{ $axes[0]['key'] }}">
                <div class="flex items-center gap-1.5">
                    <h3 class="font-semibold text-sm">{{ $axes[0]['title'] }}</h3>
                    <span class="badge badge-ghost badge-sm text-xs">{{ $axisCount($axes[0]) }}</span>
                </div>

                @if ($axes[0]['chips'] !== null)
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($axes[0]['chips'] as $chip)
                            @if (! $chip['current'] && filled($chip['href']))
                                <a href="{{ $chip['href'] }}" class="rounded-field border border-base-300 px-2.5 py-1 text-sm hover:bg-base-200 transition-colors">
                                    <span class="link link-primary">{{ $chip['label'] }}</span>
                                </a>
                            @else
                                <span class="rounded-field border border-base-300 px-2.5 py-1 text-sm opacity-60">{{ $chip['label'] }}</span>
                            @endif
                        @endforeach
                    </div>
                @else
                    <ul>
                        @foreach ($axes[0]['rows'] as $row)
                            @if (! $row['current'] && filled($row['href']))
                                <li class="border-b border-base-200 last:border-b-0">
                                    <a href="{{ $row['href'] }}" class="flex items-center gap-3 px-3 py-2.5 hover:bg-base-200 transition-colors">
                                        <span class="text-sm font-medium truncate link link-primary">{{ $row['label'] }}</span>
                                        <span class="text-xs text-subtle shrink-0 ms-auto">{{ $row['meta'] }}</span>
                                        <x-icon name="chevron-right" class="size-4 text-muted shrink-0" />
                                    </a>
                                </li>
                            @else
                                <li class="flex items-center gap-3 px-3 py-2.5 border-b border-base-200 last:border-b-0">
                                    <span class="text-sm font-medium opacity-60 truncate">{{ $row['label'] }}</span>
                                    <span class="text-xs text-subtle shrink-0 ms-auto">{{ $row['meta'] }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        @elseif (count($axes) >= 2)
            <div role="tablist" class="tabs tabs-bordered" data-testid="item-related-axes">
                @foreach ($axes as $index => $axis)
                    <input
                        type="radio"
                        name="item-related-axes"
                        role="tab"
                        class="tab"
                        aria-label="{{ $axis['title'] }} ({{ $axisCount($axis) }})"
                        @checked($index === 0)
                    />

                    <div role="tabpanel" class="tab-content pt-4" data-testid="item-related-axis-{{ $axis['key'] }}">
                        @if ($axis['chips'] !== null)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($axis['chips'] as $chip)
                                    @if (! $chip['current'] && filled($chip['href']))
                                        <a href="{{ $chip['href'] }}" class="rounded-field border border-base-300 px-2.5 py-1 text-sm hover:bg-base-200 transition-colors">
                                            <span class="link link-primary">{{ $chip['label'] }}</span>
                                        </a>
                                    @else
                                        <span class="rounded-field border border-base-300 px-2.5 py-1 text-sm opacity-60">{{ $chip['label'] }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <ul>
                                @foreach ($axis['rows'] as $row)
                                    @if (! $row['current'] && filled($row['href']))
                                        <li class="border-b border-base-200 last:border-b-0">
                                            <a href="{{ $row['href'] }}" class="flex items-center gap-3 px-3 py-2.5 hover:bg-base-200 transition-colors">
                                                <span class="text-sm font-medium truncate link link-primary">{{ $row['label'] }}</span>
                                                <span class="text-xs text-subtle shrink-0 ms-auto">{{ $row['meta'] }}</span>
                                                <x-icon name="chevron-right" class="size-4 text-muted shrink-0" />
                                            </a>
                                        </li>
                                    @else
                                        <li class="flex items-center gap-3 px-3 py-2.5 border-b border-base-200 last:border-b-0">
                                            <span class="text-sm font-medium opacity-60 truncate">{{ $row['label'] }}</span>
                                            <span class="text-xs text-subtle shrink-0 ms-auto">{{ $row['meta'] }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($setItemRows === [] && $axes === [])
            <div class="text-sm text-subtle">No related items available.</div>
        @endif
    </div>
</section>
