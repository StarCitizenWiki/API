@props([
    'item',
    'portsCount' => 0,
    'relatedItemsCount' => 0,
    'uexPricesCount' => 0,
])

@php
    $size = data_get($item, 'size');
    $grade = data_get($item, 'grade');
    $version = data_get($item, 'version');
    $currentItemUuid = data_get($item, 'uuid');
    $baseVariant = data_get($item, 'related_items.base_item', []);
    $baseVariantUuid = data_get($baseVariant, 'uuid');
    $baseVariantName = data_get($baseVariant, 'name');
    $mass = data_get($item, 'mass');
    $dimension = data_get($item, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');
    $volume = data_get($dimension, 'volume_converted', data_get($dimension, 'volume'));
    $volumeUnit = data_get($dimension, 'volume_converted_unit');
    $versionQuery = request()->query('version');

    $gradeLetter = match ($grade) {
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        default => $grade,
    };

    if ($length || $width || $height) {
        $dimensionsValue = sprintf('%s × %s × %sm', $length ?? '-', $width ?? '-', $height ?? '-');
    } else {
        $dimensionsValue = '-';
    }

    $baseVariantUrl = null;

    if (is_string($baseVariantUuid) && $baseVariantUuid !== '' && $baseVariantUuid !== $currentItemUuid) {
        $baseVariantUrl = route('web.items.show', $baseVariantUuid);

        if (is_string($versionQuery) && $versionQuery !== '') {
            $baseVariantUrl = url()->query($baseVariantUrl, ['version' => $versionQuery]);
        }
    }

    $columns = [
        [
            [
                'title' => 'Fitment',
                'rows' => [
                    [
                        'label' => 'Grade',
                        'value' => $gradeLetter ?? '-',
                    ],
                    [
                        'label' => 'Size',
                        'value' => $size !== null ? (string) $size : '-',
                    ],
                ],
            ],
            [
                'title' => 'Links',
                'rows' => [
                    [
                        'label' => 'Ports',
                        'value' => $portsCount > 0 ? (string) $portsCount : '-',
                    ],
                    [
                        'label' => 'Related',
                        'value' => $relatedItemsCount > 0 ? (string) $relatedItemsCount : '-',
                    ],
                ],
            ],
        ],
        [
            [
                'title' => 'Physical',
                'rows' => [
                    [
                        'label' => 'Mass',
                        'value' => $mass !== null ? fmt_value_with_unit($mass, 'kg', 0) : '-',
                    ],
                    [
                        'label' => 'Volume',
                        'value' => $volume !== null ? fmt_value_with_unit($volume, $volumeUnit ?? '', 0) : '-',
                    ],
                    [
                        'label' => 'Dimensions',
                        'value' => $dimensionsValue,
                    ],
                ],
            ],
            [
                'title' => 'Stats',
                'rows' => [
                    [
                        'label' => 'UEX Listings',
                        'value' => $uexPricesCount > 0 ? (string) $uexPricesCount : '-',
                    ],
                    [
                        'label' => 'Version',
                        'value' => $version ?? '-',
                    ],
                    ...($baseVariantUrl !== null ? [[
                        'label' => 'Base Variant',
                        'value' => $baseVariantName ?? 'View',
                        'url' => $baseVariantUrl,
                        'test_id' => 'item-quick-facts-base-variant-link',
                    ]] : []),
                ],
            ],
        ],
    ];
@endphp

<section {{ $attributes->merge(['class' => 'card h-full border border-base-300 bg-base-100 shadow', 'data-testid' => 'item-quick-facts-card']) }}>
    <div class="card-body p-5 sm:p-6">
        <div class="grid h-full gap-6 sm:grid-cols-2 sm:gap-8">
            @foreach ($columns as $column)
                <div class="space-y-6">
                    @foreach ($column as $section)
                        <section class="min-w-0 space-y-3">
                            <div class="text-sm font-semibold text-base-content/65">
                                {{ $section['title'] }}
                            </div>

                            <dl class="space-y-2">
                                @foreach ($section['rows'] as $row)
                                    <div class="grid grid-cols-2 items-start gap-x-3">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                            {{ $row['label'] }}
                                        </dt>
                                        <dd class="text-right text-sm font-semibold text-base-content">
                                            @if (! empty($row['url']))
                                                <a
                                                    href="{{ $row['url'] }}"
                                                    class="link link-hover link-primary"
                                                    @if (! empty($row['test_id'])) data-testid="{{ $row['test_id'] }}" @endif
                                                >
                                                    {{ $row['value'] }}
                                                </a>
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
            @endforeach
        </div>
    </div>
</section>
