@props([
    'prices',
])

@php
    $pricesList = is_array($prices) ? $prices : [];
    $pricesCount = count($pricesList);
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="credit-card" class="size-4 text-primary" />
            <span>UEX Prices</span>
            @if ($pricesCount > 0)
                <span class="badge badge-ghost text-xs">{{ $pricesCount }}</span>
            @endif
        </h2>

        @if ($pricesList === [])
            <div class="text-sm text-base-content/70">No prices available.</div>
        @else

            <div class="overflow-x-auto ">
                <table class="table table-sm">
                    <caption class="sr-only">UEX prices for this item</caption>
                    <thead>
                        <tr>
                            <th scope="col">Terminal</th>
                            <th scope="col">Buy Price</th>
                            <th scope="col">Sell Price</th>
                            <th scope="col">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pricesList as $price)
                            <tr>
                                <td class="whitespace-nowrap">{{ data_get($price, 'terminal_name', '-') }}</td>
                                <td>
                                    {{ data_get($price, 'price_buy') !== null ? number_format((float) data_get($price, 'price_buy')) . ' aUEC' : '—' }}
                                </td>
                                <td>
                                    {{ data_get($price, 'price_sell') !== null ? number_format((float) data_get($price, 'price_sell')) . ' aUEC' : '—' }}
                                </td>
                                <td>
                                    @if ($dateUpdated = data_get($price, 'date_updated'))
                                        @php
                                            $carbon = \Illuminate\Support\Carbon::make($dateUpdated);
                                        @endphp
                                        {{ $carbon ? $carbon->diffForHumans() : '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
