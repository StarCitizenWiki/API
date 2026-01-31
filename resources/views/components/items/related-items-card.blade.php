@props([
    'setItems',
    'setName',
    'variants',
    'baseVariant',
])

@php
    $setItemCount = is_array($setItems) ? count($setItems) : 0;
    $variantCount = is_array($variants) ? count($variants) : 0;
    $baseVariantCount = !empty($baseVariant) ? 1 : 0;
    $totalItemsCount = $setItemCount + $variantCount + $baseVariantCount;
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        <span class="flex items-center gap-2">
            <span>Related Items</span>
            @if ($totalItemsCount > 0)
                <span class="badge badge-ghost text-xs">{{ $totalItemsCount }}</span>
            @endif
        </span>
    </summary>
    <div class="collapse-content">
        <div class="space-y-4">
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
                    <div class="overflow-x-auto hidden sm:block">
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
            @else
                <div class="text-sm text-base-content/70">No Set Items available.</div>
            @endif

            @if (is_array($variants) && $variants !== [])
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
                                @if (! empty($baseVariant))
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
            @else
                <div class="text-sm text-base-content/70">No variants available.</div>
            @endif
        </div>
    </div>
</details>
