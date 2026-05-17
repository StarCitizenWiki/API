@use('App\Support\Format')
@php use Illuminate\Support\Carbon; @endphp
@props([
    'sections',
    'title' => 'UEX Prices',
])

@php
    $allPrices = collect($sections)->flatMap(fn (array $s): array => $s['prices'] ?? []);
    $totalCount = $allPrices->count();
@endphp

<section data-testid="uex-prices-card" {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
    <div class="card-body p-5 sm:p-6">
        <div class="flex items-center gap-2 text-sm font-semibold text-subtle">
            <span>{{ $title }}</span>
            <span class="badge badge-soft text-xs">{{ $totalCount }}</span>
        </div>

        @if ($totalCount === 0)
            <div class="text-sm text-subtle mt-3">No prices available.</div>
        @else
            <div class="space-y-6 mt-3 max-h-96 overflow-auto">
                @foreach ($sections as $section)
                    @php
                        $sectionPrices = collect($section['prices'] ?? []);
                        $sectionTitle = $section['title'] ?? null;
                        $sectionCount = $sectionPrices->count();

                        if ($sectionCount === 0) {
                            continue;
                        }

                        $hasRental = $sectionPrices->contains(fn (array $p): bool => data_get($p, 'price_rent', 0) > 0);
                        $hasSell = $sectionPrices->contains(fn (array $p): bool => data_get($p, 'price_sell', 0) > 0);

                        if ($hasRental) {
                            $priceField = 'price_rent';
                            $showBuySell = false;
                        } elseif ($hasSell) {
                            $priceField = null;
                            $showBuySell = true;
                        } else {
                            $priceField = 'price_buy';
                            $showBuySell = false;
                        }

                        $grouped = $sectionPrices
                            ->sortBy([['starmap_location.star_system_name', 'asc'], ['date_updated', 'desc']])
                            ->groupBy(fn (array $item): string => data_get($item, 'starmap_location.star_system_name') ?? 'Unknown')
                            ->all();

                        $colspan = $showBuySell ? 7 : 6;
                    @endphp

                    <section>
                        @if ($sectionTitle)
                            <div class="flex items-center gap-2 text-sm font-semibold text-subtle mb-3">
                                <span>{{ $sectionTitle }}</span>
                                <span class="badge badge-soft text-xs">{{ $sectionCount }}</span>
                            </div>
                        @endif

                        <table class="table table-sm table-zebra">
                            <thead>
                                <tr>
                                    <th scope="col" class="sticky top-0 z-20 bg-base-100">Location</th>
                                    <th scope="col" class="sticky top-0 z-20 bg-base-100">Terminal</th>
                                    @if ($showBuySell)
                                        <th scope="col" class="sticky top-0 z-20 bg-base-100">Buy</th>
                                        <th scope="col" class="sticky top-0 z-20 bg-base-100">Sell</th>
                                    @else
                                        <th scope="col" class="sticky top-0 z-20 bg-base-100">Price</th>
                                    @endif
                                    <th scope="col" class="sticky top-0 z-20 bg-base-100">Version</th>
                                    <th scope="col" class="sticky top-0 z-20 bg-base-100">Updated</th>
                                    <th scope="col" class="sticky top-0 z-20 bg-base-100">UEX</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($grouped as $systemName => $systemPrices)
                                    <tr>
                                        <th colspan="{{ $colspan }}" scope="colgroup" class="text-sm font-semibold text-subtle bg-base-200/50 sticky top-8 z-10">{{ $systemName }}</th>
                                    </tr>
                                    @foreach ($systemPrices as $price)
                                        <tr>
                                            <td class="whitespace-nowrap text-xs text-subtle">
                                                @if ($locationUrl = data_get($price, 'starmap_location.web_url'))
                                                    <a href="{{ $locationUrl }}" class="link link-hover link-primary">{{ data_get($price, 'starmap_location.parent_name', '-') }}</a>
                                                @else
                                                    {{ data_get($price, 'starmap_location.parent_name', '-') }}
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap">
                                                @if ($locationUrl = data_get($price, 'starmap_location.web_url'))
                                                    <a href="{{ $locationUrl }}" class="link link-hover link-primary">{{ data_get($price, 'terminal_name', '-') }}</a>
                                                @else
                                                    {{ data_get($price, 'terminal_name', '-') }}
                                                @endif
                                            </td>
                                            @if ($showBuySell)
                                                <td>
                                                    {{ data_get($price, 'price_buy') > 0 ? Format::number((float) data_get($price, 'price_buy')) . ' aUEC' : '-' }}
                                                </td>
                                                <td>
                                                    {{ data_get($price, 'price_sell') > 0 ? Format::number((float) data_get($price, 'price_sell')) . ' aUEC' : '-' }}
                                                </td>
                                            @else
                                                <td>
                                                    {{ data_get($price, $priceField) > 0 ? Format::number((float) data_get($price, $priceField)) . ' aUEC' : '-' }}
                                                </td>
                                            @endif
                                            <td class="whitespace-nowrap text-xs text-subtle">
                                                {{ data_get($price, 'game_version', '-') }}
                                            </td>
                                            <td class="whitespace-nowrap text-xs text-subtle">
                                                @if ($dateUpdated = data_get($price, 'date_updated'))
                                                    {{ Carbon::make($dateUpdated)?->diffForHumans() ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap text-xs">
                                                @if ($uexLink = data_get($price, 'uex_link'))
                                                    <a href="{{ $uexLink }}" class="link link-hover link-primary" target="_blank" rel="noopener noreferrer">UEX</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</section>
