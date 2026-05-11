@use('App\Support\Format')
@props(['haulingOrders'])

@php
    $kindLabel = [
        'Resource' => 'Commodity',
        'Entity' => 'Entity',
        'Entities' => 'Entity Group',
        'MissionItem' => 'Item',
    ];

    $orOrders = array_values(array_filter($haulingOrders, static fn (array $order): bool => data_get($order, 'kind') === 'Or'));
    $regularOrders = array_values(array_filter($haulingOrders, static fn (array $order): bool => data_get($order, 'kind') !== 'Or'));

    $resolveMetric = static function (array $order): ?array {
        $hasScu = (data_get($order, 'max_scu') ?? 0) > 0 || (data_get($order, 'min_scu') ?? 0) > 0;
        $hasAmount = (data_get($order, 'max_amount') ?? 0) > 0 || (data_get($order, 'min_amount') ?? 0) > 0;

        if ($hasScu) {
            return ['value' => Format::range(data_get($order, 'min_scu'), data_get($order, 'max_scu'), 'SCU', 0), 'style' => 'badge-info'];
        }

        if ($hasAmount) {
            return ['value' => Format::range(data_get($order, 'min_amount'), data_get($order, 'max_amount'), '×', 0), 'style' => 'badge-warning'];
        }

        return null;
    };

    $resolveRowProps = static function (array $order) use ($kindLabel, $resolveMetric): array {
        $kind = data_get($order, 'kind', 'Order');

        return [
            'metric' => $resolveMetric($order),
            'name' => data_get($order, 'name', '-'),
            'kindLabel' => $kindLabel[$kind] ?? $kind,
            'hasLink' => data_get($order, 'web_url') && $kind !== 'Entities',
            'webUrl' => data_get($order, 'web_url'),
            'isCommodity' => $kind === 'Resource',
            'items' => data_get($order, 'items') ?? [],
        ];
    };
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow', 'data-testid' => 'mission-hauling-section']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex items-center gap-2">
            <h2 class="card-title text-base">Hauling Orders</h2>
            <span class="badge badge-soft text-xs">{{ count($haulingOrders) }}</span>
        </div>

        @if ($regularOrders !== [])
            <div class="list">
                @foreach ($regularOrders as $order)
                    @include('components.missions.partials.hauling-order-row', $resolveRowProps($order))
                @endforeach
            </div>
        @endif

        @foreach ($orOrders as $order)
            @php
                $orOptions = data_get($order, 'or_options') ?? [];
            @endphp

            <details class="collapse collapse-arrow border border-base-300 bg-base-200/50">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    <span class="badge badge-accent badge-sm mr-2">Choice</span>
                    Deliver one of the following
                </summary>

                <div class="collapse-content">
                    <div class="list">
                        @foreach ($orOptions as $optionGroup)
                            @foreach ($optionGroup as $groupEntry)
                                @include('components.missions.partials.hauling-order-row', $resolveRowProps($groupEntry))
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</section>
