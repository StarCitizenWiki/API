@props([
    'setItems',
    'setName',
    'variants',
    'baseVariant',
    'currentItemUuid',
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
@endphp

<section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow', 'data-testid' => 'item-related-items-card']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex items-center gap-2">
            <h2 class="card-title text-base">Related Items</h2>
            @if ($totalItemsCount > 0)
                <span class="badge badge-ghost text-xs">{{ $totalItemsCount }}</span>
            @endif
        </div>

        <div class="space-y-4 overflow-y-auto pr-1 sm:max-h-96">
            @if (is_array($setItems) && $setItems !== [])
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold">Set Items: {{ $setName ?? 'Unknown Set' }}</h3>
                    <div class="grid gap-2 sm:hidden">
                        @foreach ($setItems as $setItem)
                            <div class="card border border-base-300 bg-base-100 shadow-sm">
                                <div class="card-body gap-2 p-3">
                                    <div class="text-sm font-semibold">{{ $setItem['name'] ?? '-' }}</div>
                                    <div class="text-xs text-base-content/70">
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
                    <h3 class="text-sm font-semibold">Variants</h3>
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
                </div>
            @elseif ($setItemCount === 0)
                <div class="text-sm text-base-content/70">No related items available.</div>
            @endif
        </div>
    </div>
</section>
