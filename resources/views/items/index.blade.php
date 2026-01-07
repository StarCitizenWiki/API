@extends('layouts.app')

@section('title', 'Items')
@section('meta_description', 'Browse Items.')

@section('content')
    @php
        $tableId = 'items-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];
        $tableConfig = [
            'endpoint' => route('items.index', $versionParams),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'initialFilters' => $initialFilters ?? null,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => [
                'manufacturer.name' => 'manufacturer',
                'type' => 'type',
                'sub_type' => 'sub_type',
                'classification' => 'classification',
                'size' => 'size',
                'grade' => 'grade',
                'class' => 'class',
            ],
            'apiUrlTargetId' => 'items-api-url',
            'columns' => [
                ['title' => 'Name', 'field' => 'name', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 220, 'frozen' => true, 'formatter' => 'link', 'formatterParams' => ['labelField' => 'name', 'target' => 'blank', 'urlField' => 'web_url']],
                ['title' => 'Class Name', 'field' => 'class_name', 'sortField' => 'class_name', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 220],
                ['title' => 'Manufacturer', 'field' => 'manufacturer.name', 'sortField' => 'manufacturer.name', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 200],
                ['title' => 'Type', 'field' => 'type', 'sortField' => 'type', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 200, 'formatter' => 'link', 'formatterParams' => ['labelField' => 'type', 'urlField' => 'type_web_url']],
                ['title' => 'Sub Type', 'field' => 'sub_type', 'sortField' => 'sub_type', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 200],
                ['title' => 'Classification', 'field' => 'classification', 'sortField' => 'classification', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 220],
                ['title' => 'Size', 'field' => 'size', 'sorter' => 'number', 'sortField' => 'size', 'headerSort' => true, 'headerFilter' => 'list', 'hozAlign' => 'right', 'width' => 110],
                ['title' => 'Grade', 'field' => 'grade', 'sortField' => 'grade', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 120],
                ['title' => 'Class', 'field' => 'class', 'sortField' => 'class', 'headerSort' => true, 'headerFilter' => 'list', 'minWidth' => 140],
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
            <h1 class="text-2xl font-semibold tracking-tight">Items</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
    </div>
@endsection
