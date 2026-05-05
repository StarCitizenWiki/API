@php use Illuminate\Support\Carbon; @endphp
@props([
    'purchasePrices',
    'rentalPrices',
])

@php
    $purchaseGrouped = collect(is_array($purchasePrices) ? $purchasePrices : [])
        ->sortBy([['starmap_location.star_system_name', 'asc'], ['date_updated', 'desc']])
        ->groupBy(fn (array $item): string => data_get($item, 'starmap_location.star_system_name') ?? 'Unknown')
        ->all();
    $rentalGrouped = collect(is_array($rentalPrices) ? $rentalPrices : [])
        ->sortBy([['starmap_location.star_system_name', 'asc'], ['date_updated', 'desc']])
        ->groupBy(fn (array $item): string => data_get($item, 'starmap_location.star_system_name') ?? 'Unknown')
        ->all();
    $purchaseCount = array_sum(array_map('count', $purchaseGrouped));
    $rentalCount = array_sum(array_map('count', $rentalGrouped));
    $hasPrices = $purchaseCount > 0 || $rentalCount > 0;
@endphp

@if ($hasPrices)
    <section
        data-testid="uex-prices-card" {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body max-h-96 overflow-y-auto p-5 sm:p-6">
            <div class="space-y-6">
                @if ($purchaseCount > 0)
                    <section class="space-y-3">
                        <div class="flex items-center gap-2 text-sm font-semibold text-subtle">
                            <span>Purchase Prices</span>
                            <span class="badge badge-ghost text-xs">{{ $purchaseCount }}</span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($purchaseGrouped as $systemName => $systemPrices)
                                <section>
                                    <div class="text-sm font-semibold text-subtle">{{ $systemName }}</div>
                                    <div class="overflow-x-auto">
                                        <table class="table table-sm">
                                            <caption class="sr-only">UEX purchase prices for this vehicle in {{ $systemName }}</caption>
                                            <thead>
                                            <tr>
                                                <th scope="col">Location</th>
                                                <th scope="col">Terminal</th>
                                                <th scope="col">Price</th>
                                                <th scope="col">Version</th>
                                                <th scope="col">Updated</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($systemPrices as $price)
                                                <tr>
                                                    <td class="whitespace-nowrap text-xs text-subtle">
                                                        {{ data_get($price, 'starmap_location.parent_name', '—') }}
                                                    </td>
                                                    <td class="whitespace-nowrap">
                                                        @if ($webUrl = data_get($price, 'web_url'))
                                                            <a href="{{ $webUrl }}"
                                                               class="link link-hover link-primary">{{ data_get($price, 'terminal_name', '-') }}</a>
                                                        @else
                                                            {{ data_get($price, 'terminal_name', '-') }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ data_get($price, 'price_buy') > 0 ? number_format((float) data_get($price, 'price_buy')) . ' aUEC' : '—' }}
                                                    </td>
                                                    <td class="whitespace-nowrap text-xs text-subtle">
                                                        {{ data_get($price, 'game_version', '—') }}
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
                    </section>
                @endif

                @if ($rentalCount > 0)
                    <section class="space-y-3">
                        <div class="flex items-center gap-2 text-sm font-semibold text-subtle">
                            <span>Rental Prices</span>
                            <span class="badge badge-ghost text-xs">{{ $rentalCount }}</span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($rentalGrouped as $systemName => $systemPrices)
                                <section>
                                    <div class="text-sm font-semibold text-subtle">{{ $systemName }}</div>
                                    <div class="overflow-x-auto">
                                        <table class="table table-sm">
                                            <caption class="sr-only">UEX rental prices for this vehicle in {{ $systemName }}</caption>
                                            <thead>
                                            <tr>
                                                <th scope="col">Location</th>
                                                <th scope="col">Terminal</th>
                                                <th scope="col">Price</th>
                                                <th scope="col">Version</th>
                                                <th scope="col">Updated</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($systemPrices as $price)
                                                <tr>
                                                    <td class="whitespace-nowrap text-xs text-subtle">
                                                        {{ data_get($price, 'starmap_location.parent_name', '—') }}
                                                    </td>
                                                    <td class="whitespace-nowrap">
                                                        @if ($webUrl = data_get($price, 'web_url'))
                                                            <a href="{{ $webUrl }}"
                                                               class="link link-hover link-primary">{{ data_get($price, 'terminal_name', '-') }}</a>
                                                        @else
                                                            {{ data_get($price, 'terminal_name', '-') }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ data_get($price, 'price_rent') > 0 ? number_format((float) data_get($price, 'price_rent')) . ' aUEC' : '—' }}
                                                    </td>
                                                    <td class="whitespace-nowrap text-xs text-subtle">
                                                        {{ data_get($price, 'game_version', '—') }}
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
                    </section>
                @endif
            </div>
        </div>
    </section>
@endif
