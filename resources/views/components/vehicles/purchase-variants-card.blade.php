@use('App\Support\Format')
@php use Illuminate\Support\Carbon; @endphp
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
                    'value' => '$'.Format::number($shipMatrixMsrp),
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
                    'value' => Format::valueWithUnit($claimTime, 'min', 1),
                    'url' => null,
                ] : null,
                $expediteTime !== null ? [
                    'label' => 'Expedite',
                    'value' => Format::valueWithUnit($expediteTime, 'min', 1),
                    'url' => null,
                ] : null,
                $expediteCost !== null ? [
                    'label' => 'Cost',
                    'value' => Format::valueWithUnit($expediteCost, 'aUEC', 0),
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
    <section
        data-testid="purchase-variants-card" {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <div @class([
                'grid gap-12 grid-cols-1 lg:grid-cols-2' => $sections !== [] && ($hasLoaners || $hasSkus),
                'space-y-6' => ! ($sections !== [] && ($hasLoaners || $hasSkus)),
            ])>
                @if ($sections !== [])
                    <div class="space-y-6">
                        @foreach ($sections as $section)
                            <x-dl-section :title="$section['label']" class="min-w-0">
                                @foreach ($section['rows'] as $row)
                                    <x-dt-dd :label="$row['label']">
                                        @if ($row['url'])
                                            <a href="{{ $row['url'] }}" class="link link-primary"
                                               target="_blank" rel="noopener">{{ $row['value'] }}</a>
                                        @else
                                            {{ $row['value'] }}
                                        @endif
                                    </x-dt-dd>
                                @endforeach
                            </x-dl-section>
                        @endforeach
                    </div>
                @endif

                @if ($hasLoaners || $hasSkus)
                    <section class="space-y-4">
                        <div class="text-sm font-semibold text-subtle">Loaner & SKUs</div>

                        @if ($hasLoaners)
                            <section class="space-y-2">
                                <div class="text-xs font-light uppercase tracking-wide text-subtle">Loaners
                                </div>

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
                                                    @if (! empty($loaner['name']))
                                                        <a href="{{ $loaner['web_url'] ?? route('web.vehicles.show', ['vehicle' => $loaner['uuid'] ?? $loaner['slug'] ?? $loaner['name']]) }}"
                                                           class="link link-primary">View</a>
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
                                <div class="text-xs font-light uppercase tracking-wide text-subtle">SKUs</div>

                                <div class="overflow-x-auto overflow-y-auto max-h-48">
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
                                                    {{ Format::valueWithUnit(data_get($sku, 'price'), '$', 0) }}
                                                </td>
                                                <td class="hidden sm:table-cell">
                                                    {{ Carbon::createFromTimeString(data_get($sku, 'imported_at'))->diffForHumans() }}
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
