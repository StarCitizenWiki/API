@props(['vehicle'])

@php
    $shipMatrixName = data_get($vehicle, 'shipmatrix_name');
    $shipMatrixDescription = data_get($vehicle, 'description.en_EN');
    $shipMatrixType = data_get($vehicle, 'type');
    $shipMatrixSize = data_get($vehicle, 'size');
    $shipMatrixProductionStatus = data_get($vehicle, 'production_status');
    $shipMatrixProductionNote = data_get($vehicle, 'production_note');
    $shipMatrixMsrp = data_get($vehicle, 'msrp');
    $shipMatrixPledgeUrl = data_get($vehicle, 'pledge_url');
    $shipMatrixLoaner = data_get($vehicle, 'loaner');
    $shipMatrixSkus = data_get($vehicle, 'skus', []);
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
    <summary class="collapse-title min-h-11 py-3 font-semibold">
        Loaner & SKUs
    </summary>
    <div class="collapse-content">
        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($shipMatrixMsrp)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">MSRP</dt>
                    <dd class="text-sm font-medium">${{ number_format($shipMatrixMsrp) }}</dd>
                </div>
            @endif

            @if ($shipMatrixPledgeUrl)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pledge URL</dt>
                    <dd class="text-sm font-medium">
                        <a href="{{ $shipMatrixPledgeUrl }}" class="link link-primary" target="_blank" rel="noopener">View</a>
                    </dd>
                </div>
            @endif

            @if (is_array($shipMatrixLoaner) && $shipMatrixLoaner !== [])
                <div class="space-y-1 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Loaner</dt>
                    <dd class="text-sm font-medium">
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
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
                    </dd>
                </div>
            @endif

            @if (is_array($shipMatrixSkus) && $shipMatrixSkus !== [])
                <div class="space-y-1 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">SKUs</dt>
                    <dd class="text-sm font-medium">
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Imported At</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($shipMatrixSkus as $sku)
                                    <tr>
                                        <td>{{ $sku['title'] ?? '-' }}</td>
                                        <td>
                                            {{ fmt_value_with_unit(data_get($sku, 'price'), '$', 0) }}
                                        </td>
                                        <td>
                                            {{ \Illuminate\Support\Carbon::createFromTimeString(data_get($sku, 'imported_at'))->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </dd>
                </div>
            @endif
        </dl>
    </div>
</details>
