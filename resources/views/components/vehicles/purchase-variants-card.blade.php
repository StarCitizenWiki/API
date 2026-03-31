@props(['vehicle'])

@php
    $shipMatrixMsrp = data_get($vehicle, 'msrp');
    $shipMatrixPledgeUrl = data_get($vehicle, 'pledge_url');
    $shipMatrixLoaner = data_get($vehicle, 'loaner');
    $shipMatrixSkus = data_get($vehicle, 'skus', []);
    $insurance = data_get($vehicle, 'insurance', []);
    $claimTime = data_get($insurance, 'claim_time');
    $expediteTime = data_get($insurance, 'expedite_time');
    $expediteCost = data_get($insurance, 'expedite_cost');

    $sections = [
        [
            'label' => 'Purchase',
            'rows' => array_values(array_filter([
                $shipMatrixMsrp !== null ? [
                    'label' => 'MSRP',
                    'value' => '$'.number_format($shipMatrixMsrp),
                    'url' => null,
                ] : null,
                $shipMatrixPledgeUrl ? [
                    'label' => 'Pledge',
                    'value' => 'View',
                    'url' => $shipMatrixPledgeUrl,
                ] : null,
            ])),
        ],
        [
            'label' => 'Insurance',
            'rows' => array_values(array_filter([
                $claimTime !== null ? [
                    'label' => 'Claim',
                    'value' => fmt_value_with_unit($claimTime, 'min', 1),
                    'url' => null,
                ] : null,
                $expediteTime !== null ? [
                    'label' => 'Expedite',
                    'value' => fmt_value_with_unit($expediteTime, 'min', 1),
                    'url' => null,
                ] : null,
                $expediteCost !== null ? [
                    'label' => 'Cost',
                    'value' => fmt_value_with_unit($expediteCost, 'aUEC', 0),
                    'url' => null,
                ] : null,
            ])),
        ],
    ];

    $sections = array_values(array_filter($sections, static fn (array $section): bool => $section['rows'] !== []));
    $hasLoaners = is_array($shipMatrixLoaner) && $shipMatrixLoaner !== [];
    $hasSkus = is_array($shipMatrixSkus) && $shipMatrixSkus !== [];
@endphp

@if ($sections !== [] || $hasLoaners || $hasSkus)
    <section data-testid="purchase-variants-card" {{ $attributes->merge(['class' => 'card bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <div @class([
                'grid gap-8 xl:grid-cols-2 xl:gap-10' => $sections !== [] && ($hasLoaners || $hasSkus),
                'space-y-6' => ! ($sections !== [] && ($hasLoaners || $hasSkus)),
            ])>
                @if ($sections !== [])
                    <div class="space-y-6">
                        @foreach ($sections as $section)
                            <section class="min-w-0 space-y-3">
                                <div class="text-sm font-semibold text-base-content/65">
                                    {{ $section['label'] }}
                                </div>

                                <dl class="space-y-2">
                                    @foreach ($section['rows'] as $row)
                                        <div class="grid grid-cols-2 items-start gap-x-3">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                                {{ $row['label'] }}
                                            </dt>
                                            <dd class="text-right text-sm font-semibold text-base-content">
                                                @if ($row['url'])
                                                    <a href="{{ $row['url'] }}" class="link link-primary" target="_blank" rel="noopener">{{ $row['value'] }}</a>
                                                @else
                                                    {{ $row['value'] }}
                                                @endif
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </section>
                        @endforeach
                    </div>
                @endif

                @if ($hasLoaners || $hasSkus)
                    <section class="space-y-4">
                        <div class="text-sm font-semibold text-base-content/65">Loaner & SKUs</div>

                        @if ($hasLoaners)
                            <section class="space-y-2">
                                <div class="text-xs font-medium uppercase tracking-wide text-base-content/45">Loaners</div>

                                <div class="overflow-x-auto">
                                    <table class="table table-auto table-sm">
                                        <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>View</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($shipMatrixLoaner as $loaner)
                                            <tr>
                                                <td>{{ $loaner['name'] ?? '-' }}</td>
                                                <td>
                                                    @if (! empty($loaner['link']))
                                                        <a href="{{ $loaner['link'] }}" class="link link-primary">View</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif

                        @if ($hasSkus)
                            <section class="space-y-2">
                                <div class="text-xs font-medium uppercase tracking-wide text-base-content/45">SKUs</div>

                                <div class="overflow-x-auto">
                                    <table class="table table-auto table-sm" data-testid="purchase-variants-skus">
                                        <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th class="hidden sm:table-cell">Imported At</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($shipMatrixSkus as $sku)
                                            <tr>
                                                <td>{{ $sku['title'] ?? '-' }}</td>
                                                <td>
                                                    {{ fmt_value_with_unit(data_get($sku, 'price'), '$', 0) }}
                                                </td>
                                                <td class="hidden sm:table-cell">
                                                    {{ \Illuminate\Support\Carbon::createFromTimeString(data_get($sku, 'imported_at'))->diffForHumans() }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif
                    </section>
                @endif
            </div>
        </div>
    </section>
@endif
