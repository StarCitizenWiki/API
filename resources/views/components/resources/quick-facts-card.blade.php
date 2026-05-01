@props(['resource'])

@php
    $uuid = data_get($resource, 'uuid');
    $kind = data_get($resource, 'kind');
    $signature = data_get($resource, 'signature');
    $density = data_get($resource, 'density_g_per_cc');
    $instability = data_get($resource, 'instability');
    $resistance = data_get($resource, 'resistance');
    $refinedVersion = data_get($resource, 'refined_version');
    $refinedName = data_get($refinedVersion, 'name');
    $refinedUrl = data_get($refinedVersion, 'web_url');
    $rawVersions = data_get($resource, 'raw_versions', []);
    $boxSizes = data_get($resource, 'box_sizes_scu', []);
    $systems = data_get($resource, 'systems', []);
    $methods = data_get($resource, 'methods', []);

    $propertiesRows = [
        ['label' => 'Kind', 'value' => $kind ? ucfirst($kind) : '-'],
    ];

    if ($refinedUrl) {
        $propertiesRows[] = [
            'label' => 'Refines To',
            'value' => $refinedName,
            'type' => 'link',
            'url' => $refinedUrl,
        ];
    } else {
        $propertiesRows[] = ['label' => 'Refines To', 'value' => $refinedName ?? '-'];
    }

    if (is_array($rawVersions) && $rawVersions !== []) {
        $rawLinks = [];
        foreach ($rawVersions as $rawVersion) {
            $name = $rawVersion['name'] ?? '';
            $url = $rawVersion['web_url'] ?? null;
            $rawLinks[] = ['name' => $name, 'url' => $url];
        }
        $propertiesRows[] = [
            'label' => 'Raw Versions',
            'value' => $rawLinks,
            'type' => 'links',
        ];
    }

    $propertiesRows[] = ['label' => 'Signature', 'value' => $signature ?? '-'];
    $propertiesRows[] = ['label' => 'Density', 'value' => $density !== null ? $density . ' g/cc' : '-'];
    $propertiesRows[] = ['label' => 'Instability', 'value' => $instability ?? '-'];
    $propertiesRows[] = ['label' => 'Resistance', 'value' => $resistance ?? '-'];

    $rightSections = [
        [
            'title' => 'Mining Methods',
            'rows' => $methods !== []
                ? [['label' => 'Methods', 'value' => $methods, 'type' => 'badges']]
                : [],
        ],
        [
            'title' => 'Systems',
            'rows' => $systems !== []
                ? [['label' => 'Systems', 'value' => $systems, 'type' => 'badges_ghost']]
                : [],
        ],
    ];

    if (is_array($boxSizes) && $boxSizes !== []) {
        $rightSections[] = [
            'title' => 'Box Sizes (SCU)',
            'rows' => [['label' => 'Sizes', 'value' => $boxSizes, 'type' => 'badges']],
        ];
    }

    $columns = [
        [
            ['title' => 'Properties', 'rows' => $propertiesRows],
        ],
        $rightSections,
    ];

    $uuidApiUrl = $uuid !== null ? route('commodities.show', $uuid) : null;

    $footer = [
        ['label' => 'UUID', 'value' => $uuid, 'url' => $uuidApiUrl],
    ];
@endphp

<x-quick-facts-card :columns="$columns" :footer="$footer" :test-id="'resource-quick-facts'" {{ $attributes }} />
