@props([
    'item',
    'portsCount' => 0,
    'relatedItemsCount' => 0,
    'uexPricesCount' => 0,
    'composition' => [],
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
    $trueDimension = data_get($dimension, 'true_dimension');
    $trueLength = data_get($trueDimension, 'length');
    $trueWidth = data_get($trueDimension, 'width');
    $trueHeight = data_get($trueDimension, 'height');
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

    if ($trueDimension && ($trueLength || $trueWidth || $trueHeight)) {
        $dimensionsValue = sprintf('%s × %s × %sm', $trueLength ?? '-', $trueWidth ?? '-', $trueHeight ?? '-');
        $dimensionsTitle = sprintf('UI: %s × %s × %sm', $length ?? '-', $width ?? '-', $height ?? '-');
    } elseif ($length || $width || $height) {
        $dimensionsValue = sprintf('%s × %s × %sm', $length ?? '-', $width ?? '-', $height ?? '-');
        $dimensionsTitle = null;
    } else {
        $dimensionsValue = '-';
        $dimensionsTitle = null;
    }

    $baseVariantUrl = null;

    if (is_string($baseVariantUuid) && $baseVariantUuid !== '' && $baseVariantUuid !== $currentItemUuid) {
        $baseVariantUrl = route('web.items.show', $baseVariantUuid);

        if (is_string($versionQuery) && $versionQuery !== '') {
            $baseVariantUrl = url()->query($baseVariantUrl, ['version' => $versionQuery]);
        }
    }

    $matchedCommodities = [];
    $compositionEntries = is_array($composition) ? $composition : [];
    foreach ($compositionEntries as $compEntry) {
        $commodity = data_get($compEntry, 'commodity');
        if ($commodity && ($commodityName = data_get($commodity, 'name')) && ($commodityUuid = data_get($commodity, 'uuid'))) {
            $commodityUrl = route('web.commodities.show', $commodityUuid);
            if (is_string($versionQuery) && $versionQuery !== '') {
                $commodityUrl = url()->query($commodityUrl, ['version' => $versionQuery]);
            }
            $matchedCommodities[] = ['name' => $commodityName, 'url' => $commodityUrl];
        }
    }

    $matchedBlueprints = [];
    $blueprintEntries = data_get($item, 'is_craftable') ? data_get($item, 'blueprint', []) : [];
    foreach (is_array($blueprintEntries) ? $blueprintEntries : [] as $bpEntry) {
        $bpName = data_get($bpEntry, 'output_name');
        $bpUrl = data_get($bpEntry, 'web_url') ?? data_get($bpEntry, 'link');
        if (is_string($bpName) && $bpName !== '' && is_string($bpUrl) && $bpUrl !== '') {
            $matchedBlueprints[] = ['name' => $bpName, 'url' => $bpUrl];
        }
    }

    $linksRows = [
        ['label' => 'Ports', 'value' => $portsCount > 0 ? (string) $portsCount : '-'],
        ['label' => 'Related', 'value' => $relatedItemsCount > 0 ? (string) $relatedItemsCount : '-'],
    ];

    if ($matchedCommodities !== []) {
        $linksRows[] = ['label' => 'Commodities', 'value' => $matchedCommodities, 'type' => 'links'];
    }

    if ($matchedBlueprints !== []) {
        $linksRows[] = ['label' => 'Blueprints', 'value' => $matchedBlueprints, 'type' => 'links'];
    }

    $statsRows = [
        ['label' => 'UEX Listings', 'value' => $uexPricesCount > 0 ? (string) $uexPricesCount : '-'],
    ];

    if ($baseVariantUrl !== null) {
        $statsRows[] = [
            'label' => 'Base Variant',
            'value' => $baseVariantName ?? 'View',
            'type' => 'link',
            'url' => $baseVariantUrl,
            'test_id' => 'item-quick-facts-base-variant-link',
        ];
    }

    $columns = [
        [
            [
                'title' => 'Fitment',
                'rows' => [
                    ['label' => 'Grade', 'value' => $gradeLetter ?? '-'],
                    ['label' => 'Size', 'value' => $size !== null ? (string) $size : '-'],
                ],
            ],
            [
                'title' => 'Links',
                'rows' => $linksRows,
            ],
        ],
        [
            [
                'title' => 'Physical',
                'rows' => [
                    ['label' => 'Mass', 'value' => $mass !== null ? fmt_value_with_unit($mass, 'kg', 0) : '-'],
                    ['label' => 'Volume', 'value' => $volume !== null ? fmt_value_with_unit($volume, $volumeUnit ?? '', 0) : '-'],
                    [
                        'label' => 'Dimensions',
                        'value' => $dimensionsValue,
                        ...($dimensionsTitle !== null ? ['title' => $dimensionsTitle] : []),
                    ],
                ],
            ],
            [
                'title' => 'Stats',
                'rows' => $statsRows,
            ],
        ],
    ];

    $className = data_get($item, 'class_name');
    $uuidApiUrl = $currentItemUuid !== null ? route('items.show', $currentItemUuid) : null;

    $footer = [
        ['label' => 'Class Name', 'value' => $className],
        $currentItemUuid !== null ? ['label' => 'UUID', 'value' => $currentItemUuid, 'url' => $uuidApiUrl] : ['label' => 'UUID', 'value' => '-'],
        ['label' => 'Version', 'value' => $version ?? '-'],
    ];
@endphp

<x-quick-facts-card :columns="$columns" :footer="$footer" :test-id="'item-quick-facts-card'" {{ $attributes }} />
