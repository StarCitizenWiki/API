@props([
    'setItems',
    'setName',
    'variants',
    'baseVariant',
    'currentItemUuid',
    'classification',
])

@php
    $setItemCount = is_array($setItems) ? count($setItems) : 0;
    $variantCount = is_array($variants) ? count($variants) : 0;
    $baseVariantUuid = data_get($baseVariant, 'uuid');
    $isSelfReferential = $currentItemUuid !== null && $baseVariantUuid === $currentItemUuid;

    $showBaseVariant = !empty($baseVariant) && (!$isSelfReferential || $variantCount > 0);
    $showsVariantSection = $showBaseVariant || (is_array($variants) && $variants !== []);
    $baseVariantCount = $showBaseVariant ? 1 : 0;
    $totalItemsCount = $setItemCount + $variantCount + $baseVariantCount;
    $isShipItem = is_string($classification) && str_starts_with($classification, 'Ship.');
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow', 'data-testid' => 'item-related-items-card']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex items-center gap-2">
            <h2 class="card-title text-base">Related Items</h2>
            @if ($totalItemsCount > 0)
                <span class="badge badge-soft text-xs">{{ $totalItemsCount }}</span>
            @endif
        </div>

        <div class="space-y-4 overflow-y-auto pr-1 sm:max-h-96">
            @if (is_array($setItems) && $setItems !== [])
                <div class="space-y-2">
                    <h3 class="font-semibold uppercase text-subtle">Set Items: {{ $setName ?? 'Unknown Set' }}</h3>
                    <div class="grid gap-2 sm:hidden">
                        @foreach ($setItems as $setItem)
                            <div class="card card-border bg-base-100 shadow-sm">
                                <div class="card-body gap-2 p-3">
                                    <div class="text-sm font-semibold">{{ $setItem['name'] ?? '-' }}</div>
                                    <div class="text-xs text-subtle">
                                        Slot: {{ array_last(explode('.', $setItem['classification'] ?? '')) ?? '-' }}
                                    </div>
                                    <div class="text-xs">
                                        @if (! empty($setItem['uuid']))
                                            <a href="{{ route('web.items.show', $setItem['uuid']) }}" class="link link-primary">View</a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="hidden overflow-x-auto sm:block">
                        <table class="table table-sm">
                            <caption class="sr-only">Set items linked to this item</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Slot</th>
                                    <th scope="col">Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($setItems as $setItem)
                                    <tr>
                                        <td class="whitespace-nowrap">{{ $setItem['name'] ?? '-' }}</td>
                                        <td>{{ array_last(explode('.', $setItem['classification'] ?? '')) ?? '-' }}</td>
                                        <td>
                                            @if (! empty($setItem['uuid']))
                                                <a href="{{ route('web.items.show', $setItem['uuid']) }}" class="link link-primary">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($showsVariantSection)
                <div class="space-y-2">
                    @if ($isShipItem)
                        <h3 class="font-semibold uppercase text-subtle">Component Family</h3>
                        <p class="text-xs text-subtle">Items from the same family, typically differing by size or grade.</p>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <caption class="sr-only">Component family items sharing the same base model</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Size</th>
                                        <th scope="col">Grade</th>
                                        <th scope="col">Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($showBaseVariant)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $baseVariant['name'] ?? '-' }}</td>
                                            <td>{{ $baseVariant['size'] ?? '-' }}</td>
                                            <td>{{ $baseVariant['grade_label'] ?? '-' }}</td>
                                            <td>
                                                @if (! empty($baseVariant['uuid']))
                                                    <a href="{{ route('web.items.show', $baseVariant['uuid']) }}" class="link link-primary">View</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    @foreach ($variants as $variant)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $variant['name'] ?? '-' }}</td>
                                            <td>{{ $variant['size'] ?? '-' }}</td>
                                            <td>{{ $variant['grade_label'] ?? '-' }}</td>
                                            <td>
                                                @if (! empty($variant['uuid']))
                                                    <a href="{{ route('web.items.show', $variant['uuid']) }}" class="link link-primary">View</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <h3 class="font-semibold uppercase text-subtle">Variants</h3>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <caption class="sr-only">Variant items for this base item</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Variant</th>
                                        <th scope="col">Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($showBaseVariant)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $baseVariant['name'] ?? '-' }}</td>
                                            <td>Base Item</td>
                                            <td>
                                                @if (! empty($baseVariant['uuid']))
                                                    <a href="{{ route('web.items.show', $baseVariant['uuid']) }}" class="link link-primary">View</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    @foreach ($variants as $variant)
                                        <tr>
                                            <td class="whitespace-nowrap">{{ $variant['name'] ?? '-' }}</td>
                                            <td>{{ $variant['variant_name'] ?? $variant['sub_type'] ?? $variant['type'] ?? '-' }}</td>
                                            <td>
                                                @if (! empty($variant['uuid']))
                                                    <a href="{{ route('web.items.show', $variant['uuid']) }}" class="link link-primary">View</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @elseif ($setItemCount === 0)
                <div class="text-sm text-subtle">No related items available.</div>
            @endif
        </div>
    </div>
</section>
