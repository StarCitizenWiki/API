@use('App\Support\Format')
@props(['haulingOrders'])

@php
    $kindBadge = [
        'Resource' => 'badge-info',
        'Entity' => 'badge-warning',
        'Entities' => 'badge-warning',
        'MissionItem' => 'badge-ghost',
        'Or' => 'badge-accent',
    ];

    $kindLabel = [
        'Resource' => 'Commodity',
        'Entity' => 'Entity',
        'Entities' => 'Entity Group',
        'MissionItem' => 'Item',
        'Or' => 'Choice',
    ];

    $orOrders = array_values(array_filter($haulingOrders, static fn (array $order): bool => data_get($order, 'kind') === 'Or'));
    $regularOrders = array_values(array_filter($haulingOrders, static fn (array $order): bool => data_get($order, 'kind') !== 'Or'));
@endphp

<section {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="flex items-center gap-3">
        <h2 class="text-lg font-semibold tracking-tight">Hauling Orders</h2>
        <span class="badge badge-ghost badge-sm">{{ count($haulingOrders) }}</span>
    </div>

    @foreach ($orOrders as $order)
        @php
            $badge = $kindBadge['Or'] ?? 'badge-ghost';
            $orOptions = data_get($order, 'or_options') ?? [];
        @endphp

        <div class="card card-border bg-base-100 shadow">
            <div class="card-body p-5 sm:p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="badge {{ $badge }} badge-sm">Choice</span>
                    <span class="text-sm text-subtle">Deliver one of the following</span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($orOptions as $optionIdx => $optionGroup)
                        <div class="rounded-lg border border-dashed border-base-300 bg-base-200 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-muted mb-2">
                                Choice {{ $optionIdx + 1 }} of {{ count($orOptions) }}
                            </div>

                            @foreach ($optionGroup as $groupEntry)
                                @php
                                    $entryKind = data_get($groupEntry, 'kind', 'Order');
                                    $entryBadge = $kindBadge[$entryKind] ?? 'badge-ghost';
                                    $entryLabel = $kindLabel[$entryKind] ?? $entryKind;
                                    $entryItems = data_get($groupEntry, 'items') ?? [];
                                @endphp

                                <div class="flex items-start gap-3 mb-2 last:mb-0">
                                    <span
                                        class="badge {{ $entryBadge }} badge-sm mt-0.5 shrink-0">{{ $entryLabel }}</span>

                                    <div class="flex-1 min-w-0">
                                        @if (data_get($groupEntry, 'web_url'))
                                            <a href="{{ data_get($groupEntry, 'web_url') }}"
                                               class="link link-primary text-sm font-medium">{{ data_get($groupEntry, 'name', '—') }}</a>
                                        @else
                                            <span
                                                class="text-sm font-medium">{{ data_get($groupEntry, 'name', '—') }}</span>
                                        @endif

                                        @php
                                            $hasScu = (data_get($groupEntry, 'max_scu') ?? 0) > 0 || (data_get($groupEntry, 'min_scu') ?? 0) > 0;
                                            $hasAmount = (data_get($groupEntry, 'max_amount') ?? 0) > 0 || (data_get($groupEntry, 'min_amount') ?? 0) > 0;
                                        @endphp

                                        @if ($hasScu || $hasAmount || (data_get($groupEntry, 'max_container_size') ?? 0) > 0)
                                            <div
                                                class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-subtle">
                                                @if ($hasScu)
                                                    <span>SCU: {{ Format::range(data_get($groupEntry, 'min_scu'), data_get($groupEntry, 'max_scu'), '', 0) }}</span>
                                                @endif

                                                @if ($hasAmount)
                                                    <span>Amount: {{ Format::range(data_get($groupEntry, 'min_amount'), data_get($groupEntry, 'max_amount'), '', 0) }}</span>
                                                @endif

                                                @if ((data_get($groupEntry, 'max_container_size') ?? 0) > 0)
                                                    <span>Container: {{ Format::containerSize(data_get($groupEntry, 'max_container_size')) }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        @if ($entryItems !== [])
                                            <div class="flex flex-wrap gap-1.5 mt-2">
                                                @foreach ($entryItems as $item)
                                                    @if (data_get($item, 'web_url'))
                                                        <a href="{{ data_get($item, 'web_url') }}"
                                                           class="badge badge-outline badge-sm link link-primary">{{ data_get($item, 'name', 'Item') }}</a>
                                                    @else
                                                        <span
                                                            class="badge badge-outline badge-sm">{{ data_get($item, 'name', 'Item') }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    @if ($regularOrders !== [])
        <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
            @foreach ($regularOrders as $order)
                @php
                    $kind = data_get($order, 'kind', 'Order');
                    $badge = $kindBadge[$kind] ?? 'badge-ghost';
                    $label = $kindLabel[$kind] ?? $kind;
                    $hasScu = (data_get($order, 'max_scu') ?? 0) > 0 || (data_get($order, 'min_scu') ?? 0) > 0;
                    $hasAmount = (data_get($order, 'max_amount') ?? 0) > 0 || (data_get($order, 'min_amount') ?? 0) > 0;
                    $orderItems = data_get($order, 'items') ?? [];
                @endphp

                <div class="card card-border bg-base-100 shadow">
                    <div class="card-body p-5 sm:p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="badge {{ $badge }} badge-sm">{{ $label }}</span>

                            @if (data_get($order, 'web_url'))
                                <a href="{{ data_get($order, 'web_url') }}"
                                   class="link link-primary text-sm font-semibold">{{ data_get($order, 'name', '—') }}</a>
                            @else
                                <span class="text-sm font-semibold">{{ data_get($order, 'name', '—') }}</span>
                            @endif
                        </div>

                        @if ($hasScu || $hasAmount || (data_get($order, 'max_container_size') ?? 0) > 0)
                            <div class="flex flex-wrap gap-x-4 gap-y-1 mb-3 text-sm">
                                @if ($hasScu)
                                    <div>
                                        <span class="text-muted">SCU:</span>
                                        <span
                                            class="font-medium">{{ Format::range(data_get($order, 'min_scu'), data_get($order, 'max_scu'), '', 0) }}</span>
                                    </div>
                                @endif

                                @if ($hasAmount)
                                    <div>
                                        <span class="text-muted">Amount:</span>
                                        <span
                                            class="font-medium">{{ Format::range(data_get($order, 'min_amount'), data_get($order, 'max_amount'), '', 0) }}</span>
                                    </div>
                                @endif

                                @if ((data_get($order, 'max_container_size') ?? 0) > 0)
                                    <div>
                                        <span class="text-muted">Container:</span>
                                        <span
                                            class="font-medium">{{ Format::containerSize(data_get($order, 'max_container_size')) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($orderItems !== [])
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-muted mb-2">
                                    {{ count($orderItems) === 1 ? 'Item' : count($orderItems) . ' items' }}
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($orderItems as $item)
                                        @if (data_get($item, 'web_url'))
                                            <a href="{{ data_get($item, 'web_url') }}"
                                               class="badge badge-outline badge-sm link-primary">{{ data_get($item, 'name', 'Item') }}</a>
                                        @else
                                            <span
                                                class="badge badge-outline badge-sm">{{ data_get($item, 'name', 'Item') }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
