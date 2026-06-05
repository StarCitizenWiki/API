@use('App\Support\Format')
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
    $eventSource = data_get($item, 'event_source', []);
    $currentItemUuid = data_get($item, 'uuid');
    $baseVariant = data_get($item, 'related_items.base_item', []);
    $baseVariantUuid = data_get($baseVariant, 'uuid');
    $baseVariantName = data_get($baseVariant, 'name');
    $mass = data_get($item, 'mass');
    $dimension = data_get($item, 'dimension', []);
    $dimensionsBlock = data_get($dimension, 'dimensions');
    $cargoDimension = data_get($dimension, 'cargo_dimension');
    $uiDimension = data_get($dimension, 'ui_dimension');
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

    $dimLength = data_get($dimensionsBlock, 'length');
    $dimWidth = data_get($dimensionsBlock, 'width');
    $dimHeight = data_get($dimensionsBlock, 'height');

    if ($dimLength || $dimWidth || $dimHeight) {
        $dimensionsValue = sprintf('%s × %s × %sm', $dimLength ?? '-', $dimWidth ?? '-', $dimHeight ?? '-');
    } else {
        $dimensionsValue = '-';
    }

    $uiLength = data_get($uiDimension, 'length');
    $uiWidth = data_get($uiDimension, 'width');
    $uiHeight = data_get($uiDimension, 'height');
    $dimensionsTitle = ($uiLength || $uiWidth || $uiHeight) && $dimensionsValue !== '-'
        ? sprintf('UI: %s × %s × %sm', $uiLength ?? '-', $uiWidth ?? '-', $uiHeight ?? '-')
        : null;

    $cargoLength = data_get($cargoDimension, 'length');
    $cargoWidth = data_get($cargoDimension, 'width');
    $cargoHeight = data_get($cargoDimension, 'height');
    $cargoValue = ($cargoLength || $cargoWidth || $cargoHeight)
        ? sprintf('%s × %s × %sm', $cargoLength ?? '-', $cargoWidth ?? '-', $cargoHeight ?? '-')
        : null;

    $baseVariantUrl = null;

    if (is_string($baseVariantUuid) && $baseVariantUuid !== '' && $baseVariantUuid !== $currentItemUuid) {
        $baseVariantUrl = data_get($baseVariant, 'web_url') ?? route('web.items.show', $baseVariantUuid);
    }

    $matchedCommodities = [];
    $compositionEntries = is_array($composition) ? $composition : [];
    foreach ($compositionEntries as $compEntry) {
        $commodity = data_get($compEntry, 'commodity');
        if ($commodity && ($commodityName = data_get($commodity, 'name')) && ($commodityUuid = data_get($commodity, 'uuid'))) {
            $commodityUrl = data_get($commodity, 'web_url') ?? route('web.commodities.show', $commodityUuid);
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

    if ($eventSource !== []) {
        $statsRows[] = ['label' => 'Event Source', 'value' => implode(', ', $eventSource)];
    }

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

        [
            'title' => 'Physical',
            'rows' => [
                ['label' => 'Mass', 'value' => $mass !== null ? Format::valueWithUnit($mass, 'kg', 0) : '-'],
                ['label' => 'Volume', 'value' => $volume !== null ? Format::valueWithUnit($volume, $volumeUnit ?? '', 0) : '-'],
                [
                    'label' => 'Dimensions',
                    'value' => $dimensionsValue,
                    ...($dimensionsTitle !== null ? ['title' => $dimensionsTitle] : []),
                ],
                ...($cargoValue !== null ? [['label' => 'Cargo Size', 'value' => $cargoValue]] : []),
            ],
        ],
        [
            'title' => 'Stats',
            'rows' => $statsRows,
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
