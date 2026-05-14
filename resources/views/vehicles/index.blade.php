@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen
@endsection
@section('meta_description')
    {!! data_get($seo, 'metaDescription', 'Explore all Star Citizen vehicles including ships, ground vehicles, and gravlevs.') !!}
@endsection

@section('meta')
    <x-seo.metadata
        :canonical="data_get($seo, 'canonicalUrl')"
        :keywords="data_get($seo, 'keywords', [])"
        og-type="website"
        :og-title="data_get($seo, 'ogTitle')"
        :og-description="data_get($seo, 'ogDescription')"
        twitter-card="summary"
        :twitter-title="data_get($seo, 'twitterTitle')"
        :twitter-description="data_get($seo, 'twitterDescription')"
        :structured-data="data_get($seo, 'structuredData', [])"
    />
@endsection

@section('content')
    @php
        $booleanFilterOptions = [
            ['value' => '', 'label' => 'All'],
            ['value' => 'true', 'label' => 'Yes'],
            ['value' => 'false', 'label' => 'No'],
        ];

        $tableId = 'vehicles-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];
        $tableConfig = [
            'endpoint' => route('vehicles.index', $versionParams),
            'filterOptionsEndpoint' => route('vehicles.filters', $versionParams),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'initialFilters' => $initialFilters,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => [
                'manufacturer.name' => 'manufacturer',
                'size_class' => 'size',
                'career' => 'career',
                'role' => 'role',
                'shield.face_type' => 'shield.face_type',
                'max_medical_tier' => 'max_medical_tier',
            ],
            'apiUrlTargetId' => 'vehicles-api-url',
            'externalFilters' => [
                ['title' => 'Include Irrelevant', 'field' => 'include_irrelevant', 'options' => [
                    ['value' => '', 'label' => 'Default'],
                    ['value' => 'true', 'label' => 'Yes'],
                ]],
                ['title' => 'Ship', 'field' => 'is_spaceship', 'options' => $booleanFilterOptions],
                ['title' => 'Ground Vehicle', 'field' => 'is_vehicle', 'options' => $booleanFilterOptions],
                ['title' => 'Gravlev', 'field' => 'is_gravlev', 'options' => $booleanFilterOptions],
                ['title' => 'Medical Tier', 'field' => 'max_medical_tier'],
            ],
            'columns' => [
                ['title' => 'Name', 'field' => 'name', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 220, 'frozen' => true, 'formatter' => 'link', 'formatterParams' => ['labelField' => 'name', 'target' => 'blank', 'urlField' => 'web_url']],
                ['title' => 'Class', 'field' => 'class_name', 'sortField' => 'class_name', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 200],
                ['title' => 'Manufacturer', 'field' => 'manufacturer.name', 'sortField' => 'manufacturer.name', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 180],
                ['title' => 'Career', 'field' => 'career', 'sortField' => 'career', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 160],
                ['title' => 'Role', 'field' => 'role', 'sortField' => 'role', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 160],
                ['title' => 'Size', 'field' => 'size_class', 'sorter' => 'number', 'sortField' => 'Size', 'headerSort' => true, 'headerFilter' => 'list', 'hozAlign' => 'right', 'width' => 120],
                [
                    'title' => 'Dimensions',
                    'columns' => [
                        ['title' => 'W', 'field' => 'dimension.width', 'sorter' => 'number', 'sortField' => 'Width', 'formatter' => 'money', 'formatterParams' => ['symbol' => ' m', 'symbolAfter' => true], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                        ['title' => 'L', 'field' => 'dimension.length', 'sorter' => 'number', 'sortField' => 'Length', 'formatter' => 'money', 'formatterParams' => ['symbol' => ' m', 'symbolAfter' => true], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                        ['title' => 'H', 'field' => 'dimension.height', 'sorter' => 'number', 'sortField' => 'Height', 'formatter' => 'money', 'formatterParams' => ['symbol' => ' m', 'symbolAfter' => true], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                     ],
                ],

                ['title' => 'Crew', 'field' => 'crew.min', 'sorter' => 'number', 'sortField' => 'Crew', 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                ['title' => 'Mass Total', 'field' => 'mass_total', 'sorter' => 'number', 'sortField' => 'MassTotal', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' kg'], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 140],

                [
                    'title' => 'Cargo',
                    'columns' => [
                        ['title' => 'Cargo', 'field' => 'cargo_capacity', 'sorter' => 'number', 'sortField' => 'Cargo', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' SCU', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                        ['title' => 'Ore', 'field' => 'ore_capacity', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' SCU', 'precision' => false], 'headerSort' => false, 'hozAlign' => 'right', 'width' => 100],
                        ['title' => 'Stowage', 'field' => 'vehicle_inventory', 'sorter' => 'number', 'sortField' => 'Stowage', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' μSCU', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 150],
                    ],
                ],

                [
                    'title' => 'Durability',
                    'columns' => [
                        ['title' => 'Health', 'field' => 'health', 'sorter' => 'number', 'sortField' => 'Health', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' HP', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                        ['title' => 'Armor', 'field' => 'armor.health', 'sorter' => 'number', 'sortField' => 'Armor.Health', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' HP', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 140],
                        ['title' => 'Shield', 'field' => 'shield.hp', 'sorter' => 'number', 'sortField' => 'ShieldsTotal.Hp', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' HP', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                        ['title' => 'Shield Face', 'field' => 'shield.face_type', 'sortField' => 'ShieldController.FaceType', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 160],
                    ],
                ],

                ['title' => 'Medical', 'field' => 'max_medical_tier', 'headerSort' => true, 'headerFilter' => 'list', 'width' => 120],

                [
                    'title' => 'Speed',
                    'columns' => [
                        ['title' => 'SCM', 'field' => 'speed.scm', 'sorter' => 'number', 'sortField' => 'FlightCharacteristics.IFCS.ScmSpeed', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' m/s', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 110],
                        ['title' => 'NAV', 'field' => 'speed.max', 'sorter' => 'number', 'sortField' => 'FlightCharacteristics.IFCS.MaxSpeed', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' m/s', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                    ],
                ],

                [
                    'title' => 'X-Section',
                    'columns' => [
                        ['title' => 'L', 'field' => 'cross_section.length', 'sorter' => 'number', 'sortField' => 'CrossSection.X', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                        ['title' => 'W', 'field' => 'cross_section.width', 'sorter' => 'number', 'sortField' => 'CrossSection.Z', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                        ['title' => 'H', 'field' => 'cross_section.height', 'sorter' => 'number', 'sortField' => 'CrossSection.Y', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                    ],
                ],

                ['title' => 'Vehicle', 'field' => 'is_vehicle', 'sortField' => 'IsVehicle', 'headerSort' => true, 'visible' => false],
                ['title' => 'Gravlev', 'field' => 'is_gravlev', 'sortField' => 'IsGravlev', 'headerSort' => true, 'visible' => false],
                ['title' => 'Spaceship', 'field' => 'is_spaceship', 'sortField' => 'IsSpaceship', 'headerSort' => true, 'visible' => false],

                [
                    'title' => 'Signature',
                    'columns' => [
                        ['title' => 'IR Quantum', 'field' => 'signature.ir_quantum', 'sorter' => 'number', 'sortField' => 'Emission.IrQuantum', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 130],
                        ['title' => 'IR Shields', 'field' => 'signature.ir_shields', 'sorter' => 'number', 'sortField' => 'Emission.IrShields', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 130],
                        ['title' => 'EM Quantum', 'field' => 'signature.em_quantum', 'sorter' => 'number', 'sortField' => 'Emission.EmQuantum', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 130],
                        ['title' => 'EM Shields', 'field' => 'signature.em_shields', 'sorter' => 'number', 'sortField' => 'Emission.EmShields', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 130],
                    ],
                ],

                [
                    'title' => 'Cooling',
                    'columns' => [
                        [
                            'title' => 'Segments',
                            'field' => 'cooling.generation_segments',
                            'sortField' => 'Cooling.GenerationSegments',
                        ],
                        [
                            'title' => 'Usage (Shields %)',
                            'field' => 'cooling.usage_shields_pct',
                            'sortField' => 'Cooling.UsageShieldsPct',
                            'formatter' => 'progress',
                            'formatterParams' => [
                                'min' => 0,
                                'max' => 1,
                                'legend' => true,
                            ],
                        ],
                        [
                            'title' => 'Usage (Quantum %)',
                            'field' => 'cooling.usage_quantum_pct',
                            'sortField' => 'Cooling.UsageQuantumPct',
                            'formatter' => 'progress',
                            'formatterParams' => [
                                'min' => 0,
                                'max' => 1,
                                'legend' => true,
                            ],
                        ],
                    ],
                ],

                [
                    'title' => 'Power',
                    'columns' => [
                        [
                            'title' => 'Segments',
                            'field' => 'power.generation_segments',
                            'sortField' => 'Power.GenerationSegments',
                        ],
                        [
                            'title' => 'Usage (Shields)',
                            'field' => 'power.used_segments_shields',
                            'sortField' => 'Power.UsedSegmentsShields',
                        ],
                        [
                            'title' => 'Usage (Quantum)',
                            'field' => 'power.used_segments_quantum',
                            'sortField' => 'Power.UsedSegmentsQuantum',
                        ],
                    ],
                ],


                [
                    'title' => 'MSRP', 'field' => 'msrp', 'sortField' => 'msrp', 'headerSort' => true,
                    'formatter' => 'money',
                    'formatterParams' => ['symbol' => ' $', 'symbolAfter' => true]
                ],
                [
                    'title' => 'API Url',
                    'field' => 'uuid',
                    'formatter' => 'link',
                    'formatterParams' => [
                        'label' => 'View',
                        'target' => 'blank',
                        'urlField' => 'link',
                    ],
                    'headerSort' => false,
                    'hozAlign' => 'right',
                    'width' => 100,
                ],
            ],
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight" data-testid="vehicles-index-heading">Vehicles</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
        <x-column-source-map :columns="$tableConfig['columns']" />
    </div>
@endsection
