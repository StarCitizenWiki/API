@props([
    'setItems' => [],
    'setName' => null,
    'variants' => [],
    'baseVariant' => null,
    'currentItemUuid',
    'classification',
])

@php
    $setItems = is_array($setItems) ? $setItems : [];
    $variants = is_array($variants) ? $variants : [];
    $baseVariant = is_array($baseVariant) ? $baseVariant : null;

    $isShipItem = is_string($classification) && str_starts_with($classification, 'Ship.');
    $showBase = ! empty($baseVariant) && ($baseVariant['uuid'] ?? null) !== $currentItemUuid;
    $familyItems = $showBase ? [$baseVariant, ...$variants] : $variants;
    $familyCount = count($familyItems);

    $setItemRows = array_map(static fn (array $item): array => [
        'label' => $item['name'] ?? '-',
        'meta' => $item['sub_type'] ?? '-',
        'href' => $item['web_url'] ?? null,
        'current' => ($item['uuid'] ?? null) === $currentItemUuid,
    ], $setItems);

    $familyRows = array_map(static fn (array $item): array => [
        'label' => $item['name'] ?? '-',
        'meta' => 'Size '.($item['size'] ?? '-').' / Grade '.($item['grade_label'] ?? '-'),
        'href' => $item['web_url'] ?? null,
        'current' => ($item['uuid'] ?? null) === $currentItemUuid,
    ], $familyItems);

    $listSections = [];

    if ($setItemRows !== []) {
        $listSections[] = [
            'title' => 'Set Items',
            'count' => count($setItemRows),
            'subtitle' => $setName ? '- '.$setName : null,
            'rows' => $setItemRows,
        ];
    }

    if ($isShipItem && $familyRows !== []) {
        $listSections[] = [
            'title' => 'Component Family',
            'count' => $familyCount,
            'subtitle' => null,
            'rows' => $familyRows,
        ];
    }

    $variantChips = [];

    if ($showBase) {
        $variantChips[] = [
            'label' => 'Base',
            'href' => $baseVariant['web_url'] ?? null,
            'current' => false,
        ];
    }

    foreach ($variants as $variant) {
        $variantChips[] = [
            'label' => $variant['variant_name'] ?? '-',
            'href' => $variant['web_url'] ?? null,
            'current' => ($variant['uuid'] ?? null) === $currentItemUuid,
        ];
    }
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow', 'data-testid' => 'item-related-items-card']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <h2 class="card-title text-base">Related Items</h2>

        @foreach ($listSections as $section)
            <div class="space-y-2">
                <div class="flex items-center gap-1.5">
                    <h3 class="font-semibold text-sm">{{ $section['title'] }}</h3>
                    <span class="badge badge-ghost badge-sm text-xs">{{ $section['count'] }}</span>
                    @if ($section['subtitle'])
                        <span class="text-xs text-subtle truncate">{{ $section['subtitle'] }}</span>
                    @endif
                </div>

                <ul>
                    @foreach ($section['rows'] as $row)
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
        @endforeach

        @if (! $isShipItem && $variantChips !== [])
            <div class="space-y-2">
                <div class="flex items-center gap-1.5">
                    <h3 class="font-semibold text-sm">Variants</h3>
                    <span class="badge badge-ghost badge-sm text-xs">{{ $familyCount }}</span>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    @foreach ($variantChips as $chip)
                        @if (! $chip['current'] && filled($chip['href']))
                            <a href="{{ $chip['href'] }}" class="rounded-lg border border-base-300 px-2.5 py-1 text-sm hover:bg-base-200 transition-colors">
                                <span class="link link-primary">{{ $chip['label'] }}</span>
                            </a>
                        @else
                            <span class="rounded-lg border border-base-300 px-2.5 py-1 text-sm opacity-60">{{ $chip['label'] }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if ($setItemRows === [] && $familyCount === 0)
            <div class="text-sm text-subtle">No related items available.</div>
        @endif
    </div>
</section>
