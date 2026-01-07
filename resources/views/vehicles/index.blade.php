@extends('layouts.app')

@section('title', 'Vehicles')
@section('meta_description', 'Browse Vehicles.')

@section('content')
    @php
        $tableId = 'vehicles-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];
        $tableConfig = [
            'endpoint' => route('vehicles.index', $versionParams),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => [
                'manufacturer.name' => 'manufacturer',
                'size_class' => 'size',
                'career' => 'career',
                'role' => 'role',
                'is_vehicle' => 'is_vehicle',
                'is_gravlev' => 'is_gravlev',
                'is_spaceship' => 'is_spaceship',
                'shield.face_type' => 'shield.face_type',
            ],
            'apiUrlTargetId' => 'vehicles-api-url',
            'columns' => [
                ['title' => 'Name', 'field' => 'name','headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 220, 'frozen' => true, 'formatter' => 'link', 'formatterParams' => ['labelField' => 'name', 'target' => 'blank', 'urlField' => 'web_url']],
                ['title' => 'Class', 'field' => 'class_name', 'sortField' => 'class_name', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 200],
                ['title' => 'Manufacturer', 'field' => 'manufacturer.name', 'sortField' => 'manufacturer.name', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 180],
                ['title' => 'Size', 'field' => 'size_class', 'sorter' => 'number', 'sortField' => 'size_class', 'headerSort' => true, 'headerFilter' => 'list', 'hozAlign' => 'right', 'width' => 120],
                [
                    'title' => 'Dimensions',
                    'columns' => [
                        ['title' => 'W', 'field' => 'dimension.width', 'sorter' => 'number', 'sortField' => 'width', 'formatter' => 'money', 'formatterParams' => ['symbol' => ' m', 'symbolAfter' => true], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                        ['title' => 'L', 'field' => 'dimension.length', 'sorter' => 'number', 'sortField' => 'length', 'formatter' => 'money', 'formatterParams' => ['symbol' => ' m', 'symbolAfter' => true], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                        ['title' => 'H', 'field' => 'dimension.height', 'sorter' => 'number', 'sortField' => 'height', 'formatter' => 'money', 'formatterParams' => ['symbol' => ' m', 'symbolAfter' => true], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                     ],
                ],

                ['title' => 'Crew', 'field' => 'crew.min', 'sorter' => 'number', 'sortField' => 'crew.min', 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                ['title' => 'Mass Total', 'field' => 'mass_total', 'sorter' => 'number', 'sortField' => 'mass_total', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' kg'], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 140],

                [
                    'title' => 'Cargo',
                    'columns' => [
                        ['title' => 'Cargo', 'field' => 'cargo_capacity', 'sorter' => 'number', 'sortField' => 'cargo_capacity', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' SCU', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                        ['title' => 'Stowage', 'field' => 'vehicle_inventory', 'sorter' => 'number', 'sortField' => 'vehicle_inventory', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' SCU', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 150],
                    ],
                ],

                [
                    'title' => 'Durability',
                    'columns' => [
                        ['title' => 'Health', 'field' => 'health', 'sorter' => 'number', 'sortField' => 'health', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' HP', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                        ['title' => 'Armor', 'field' => 'armor.health', 'sorter' => 'number', 'sortField' => 'armor.health', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' HP', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 140],
                        ['title' => 'Shield', 'field' => 'shield.hp', 'sorter' => 'number', 'sortField' => 'shield.hp', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' HP', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                        ['title' => 'Shield Face', 'field' => 'shield.face_type', 'sortField' => 'shield.face_type', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 160],
                    ],
                ],

                [
                    'title' => 'Speed',
                    'columns' => [
                        ['title' => 'SCM', 'field' => 'speed.scm', 'sorter' => 'number', 'sortField' => 'speed.scm', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' m/s', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 110],
                        ['title' => 'NAV', 'field' => 'speed.max', 'sorter' => 'number', 'sortField' => 'speed.max', 'formatter' => 'money', 'formatterParams' => ['symbolAfter' => true, 'symbol' => ' m/s', 'precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                    ],
                ],

                [
                    'title' => 'X-Section',
                    'columns' => [
                        ['title' => 'L', 'field' => 'cross_section.length', 'sorter' => 'number', 'sortField' => 'cross_section.length', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                        ['title' => 'W', 'field' => 'cross_section.width', 'sorter' => 'number', 'sortField' => 'cross_section.width', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                        ['title' => 'H', 'field' => 'cross_section.height', 'sorter' => 'number', 'sortField' => 'cross_section.height', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 90],
                    ],
                ],

                ['title' => 'Vehicle', 'field' => 'is_vehicle', 'sortField' => 'is_vehicle', 'formatter' => 'tickCross', 'headerSort' => true, 'headerFilter' => 'list', 'width' => 110],
                ['title' => 'Gravlev', 'field' => 'is_gravlev', 'sortField' => 'is_gravlev', 'formatter' => 'tickCross', 'headerSort' => true, 'headerFilter' => 'list', 'width' => 110],
                ['title' => 'Spaceship', 'field' => 'is_spaceship', 'sortField' => 'is_spaceship', 'formatter' => 'tickCross', 'headerSort' => true, 'headerFilter' => 'list', 'width' => 120],

                [
                    'title' => 'Signature',
                    'columns' => [
                        ['title' => 'IR Quantum', 'field' => 'signature.ir_quantum', 'sorter' => 'number', 'sortField' => 'signature.ir_quantum', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 130],
                        ['title' => 'IR Shields', 'field' => 'signature.ir_shields', 'sorter' => 'number', 'sortField' => 'signature.ir_shields', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 130],
                        ['title' => 'EM Quantum', 'field' => 'signature.em_quantum', 'sorter' => 'number', 'sortField' => 'signature.em_quantum', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 130],
                        ['title' => 'EM Shields', 'field' => 'signature.em_shields', 'sorter' => 'number', 'sortField' => 'signature.em_shields', 'formatter' => 'money', 'formatterParams' => ['precision' => false], 'headerSort' => true, 'hozAlign' => 'right', 'width' => 130],
                    ],
                ],

                ['title' => 'Career', 'field' => 'career', 'sortField' => 'career', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 160],
                ['title' => 'Role', 'field' => 'role', 'sortField' => 'role', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 160],
                [
                    'title' => '',
                    'field' => 'uuid',
                    'formatter' => 'viewButton',
                    'formatterParams' => [
                        'label' => 'View',
                        'hrefField' => 'web_url',
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
            <h1 class="text-2xl font-semibold tracking-tight">Vehicles</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
    </div>
@endsection
