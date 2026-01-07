@extends('layouts.app')

@section('title', 'Ship-Matrix Vehicles')
@section('meta_description', 'Browse Ship-Matrix Vehicles')

@section('content')
    @php
        $tableId = 'ship-matrix-vehicles-table';
        $tableConfig = [
            'endpoint' => route('shipmatrix.vehicles.index'),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => [
                'manufacturer' => 'manufacturer',
                'size' => 'size',
                'type' => 'type',
                'focus' => 'focus',
                'production_status' => 'production_status',
            ],
            'apiUrlTargetId' => 'ship-matrix-api-url-vehicles',
            'columns' => [
                ['title' => 'ID', 'field' => 'id', 'sorter' => 'number', 'sortField' => 'id', 'headerSort' => true, 'width' => 90],
                ['title' => 'Chassis ID', 'field' => 'chassis_id', 'sorter' => 'number', 'sortField' => 'chassis_id', 'headerSort' => true, 'width' => 110],
                ['title' => 'Name', 'field' => 'name', 'sortField' => 'name', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 220],
                                [
                    'title' => 'Foci',
                    'field' => 'foci',
                    'formatter' => 'translationList',
                    'sortField' => 'focus',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Status',
                    'field' => 'production_status',
                    'formatter' => 'translationLabel',
                    'sortField' => 'production_status',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 160,
                ],
                                [
                    'title' => 'Type',
                    'field' => 'type',
                    'formatter' => 'translationLabel',
                    'sortField' => 'type',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 160,
                ],
                [
                    'title' => 'Size',
                    'field' => 'size',
                    'formatter' => 'translationLabel',
                    'sortField' => 'size',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 140,
                ],
                ['title' => 'Length', 'field' => 'dimension.length', 'sorter' => 'number', 'sortField' => 'length', 'headerSort' => true, 'hozAlign' => 'right', 'width' => 110],
                ['title' => 'Width', 'field' => 'dimension.width', 'sorter' => 'number', 'sortField' => 'width', 'headerSort' => true, 'hozAlign' => 'right', 'width' => 110],
                ['title' => 'Height', 'field' => 'dimension.height', 'sorter' => 'number', 'sortField' => 'height', 'headerSort' => true, 'hozAlign' => 'right', 'width' => 110],
                ['title' => 'Cargo', 'field' => 'cargo_capacity', 'sorter' => 'number', 'sortField' => 'cargo_capacity', 'headerSort' => true, 'hozAlign' => 'right', 'width' => 110],
                ['title' => 'Crew Min', 'field' => 'crew.min', 'sorter' => 'number', 'sortField' => 'min_crew', 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                ['title' => 'Crew Max', 'field' => 'crew.max', 'sorter' => 'number', 'sortField' => 'max_crew', 'headerSort' => true, 'hozAlign' => 'right', 'width' => 120],
                [
                    'title' => 'Note',
                    'field' => 'production_note',
                    'formatter' => 'translationLabel',
                    'headerSort' => false,
                    'minWidth' => 180,
                ],
                [
                    'title' => 'MSRP',
                    'field' => 'msrp',
                    'sorter' => 'number',
                    'sortField' => 'msrp',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 120,
                ],
                [
                    'title' => 'Manufacturer',
                    'field' => 'manufacturer',
                    'formatter' => 'objectLabel',
                    'sortField' => 'manufacturer',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => '',
                    'field' => 'id',
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
            <h1 class="text-2xl font-semibold tracking-tight">Ship-Matrix Vehicles</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
    </div>
@endsection
