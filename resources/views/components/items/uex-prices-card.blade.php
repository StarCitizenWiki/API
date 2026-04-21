@php use Illuminate\Support\Carbon; @endphp
@props([
    'prices',
])

@php
    $grouped = collect(is_array($prices) ? $prices : [])
        ->sortBy([['starmap_location.star_system_name', 'asc'], ['date_updated', 'desc']])
        ->groupBy(fn (array $item): string => data_get($item, 'starmap_location.star_system_name') ?? 'Unknown')
        ->all();
    $pricesCount = array_sum(array_map('count', $grouped));
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        <span class="flex items-center gap-2">
            <span>UEX Prices</span>
            @if ($pricesCount > 0)
                <span class="badge badge-ghost text-xs">{{ $pricesCount }}</span>
            @endif
        </span>
    </summary>
    <div class="collapse-content max-h-96 overflow-y-auto">
        @if ($grouped === [])
            <div class="text-sm text-base-content/70">No prices available.</div>
        @else
            <div class="space-y-4">
                @foreach ($grouped as $systemName => $systemPrices)
                    <section>
                        <div class="text-sm font-semibold text-base-content/65">{{ $systemName }}</div>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <caption class="sr-only">UEX prices for this item in {{ $systemName }}</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Location</th>
                                        <th scope="col">Terminal</th>
                                        <th scope="col">Buy Price</th>
                                        <th scope="col">Sell Price</th>
                                        <th scope="col">Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($systemPrices as $price)
                                        <tr>
                                            <td class="whitespace-nowrap text-xs text-base-content/70">
                                                {{ data_get($price, 'starmap_location.parent_name', '—') }}
                                            </td>
                                            <td class="whitespace-nowrap">
                                                @if ($webUrl = data_get($price, 'web_url'))
                                                    <a href="{{ $webUrl }}" class="link link-hover link-primary">{{ data_get($price, 'terminal_name', '-') }}</a>
                                                @else
                                                    {{ data_get($price, 'terminal_name', '-') }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ data_get($price, 'price_buy') > 0 ? number_format((float) data_get($price, 'price_buy')) . ' aUEC' : '—' }}
                                            </td>
                                            <td>
                                                {{ data_get($price, 'price_sell') > 0 ? number_format((float) data_get($price, 'price_sell')) . ' aUEC' : '—' }}
                                            </td>
                                            <td>
                                                @if ($dateUpdated = data_get($price, 'date_updated'))
                                                    {{ Carbon::make($dateUpdated)?->diffForHumans() ?? '—' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</details>
