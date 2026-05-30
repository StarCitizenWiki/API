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
        $minScu = data_get($order, 'min_scu');
        $maxScu = data_get($order, 'max_scu');
        $minAmount = data_get($order, 'min_amount');
        $maxAmount = data_get($order, 'max_amount');

        $hasScu = ($maxScu ?? 0) > 0 || ($minScu ?? 0) > 0;
        $hasAmount = ($maxAmount ?? 0) > 0 || ($minAmount ?? 0) > 0;

        if ($hasScu) {
            $value = ($minScu === null || $maxScu === null)
                ? Format::valueWithUnit($maxScu ?? $minScu, 'SCU', 0)
                : Format::range($minScu, $maxScu, 'SCU', 0);

            return ['value' => $value, 'style' => 'badge-info'];
        }

        if ($hasAmount) {
            $value = ($minAmount === null || $maxAmount === null)
                ? Format::valueWithUnit($maxAmount ?? $minAmount, '×', 0)
                : Format::range($minAmount, $maxAmount, '×', 0);

            return ['value' => $value, 'style' => 'badge-warning'];
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
            'containerSize' => data_get($order, 'max_container_size'),
            'items' => data_get($order, 'items') ?? [],
        ];
    };
@endphp

<section class="space-y-4" {{ $attributes->merge(['data-testid' => 'mission-hauling-section']) }}>
    <h2 class="text-lg font-semibold tracking-tight">
        Hauling Orders
        @if ($orOrders !== [])
            <span class="text-subtle font-normal text-base">(haul one of)</span>
        @endif
    </h2>

    @if ($regularOrders !== [])
        <div class="card card-border bg-base-100 shadow">
            <div class="card-body p-5 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach ($regularOrders as $order)
                        @include('components.missions.partials.hauling-order-row', $resolveRowProps($order))
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @foreach ($orOrders as $order)
        @php
            $flatOptions = [];
            foreach (data_get($order, 'or_options') ?? [] as $optionGroup) {
                foreach ($optionGroup as $groupEntry) {
                    $flatOptions[] = $groupEntry;
                }
            }
        @endphp

        <div class="card card-border bg-base-100 shadow">
            <div class="card-body p-5 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach ($flatOptions as $groupEntry)
                        @include('components.missions.partials.hauling-order-row', $resolveRowProps($groupEntry))
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</section>
