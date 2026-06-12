@extends('layouts.app')

@section('title', 'Starmap Celestial Objects')
@section('meta_description', 'Celestial objects in all star systems: planets, moons, asteroid fields, and jump points.')

@section('content')
    @php
        $tableId = 'starmap-celestial-objects-table';
        $tableConfig = [
            'endpoint' => route('celestial-objects.index', ['include' => 'starsystem']),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'apiUrlTargetId' => 'starmap-celestial-objects-api-url',
            'columns' => [
                ['title' => 'ID', 'field' => 'id', 'sorter' => 'number', 'sortField' => 'id', 'headerSort' => true, 'width' => 100],
                [
                    'title' => 'System',
                    'field' => 'starsystem.name',
                    'formatter' => 'objectLabel',
                    'sortField' => 'starsystem',
                    'headerSort' => true,
                    'headerFilter' => 'input',
                    'minWidth' => 180,
                ],
                ['title' => 'Name', 'field' => 'name', 'sortField' => 'name', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 200],
                [
                    'title' => 'Designation',
                    'field' => 'designation',
                    'sortField' => 'designation',
                    'headerSort' => true,
                    'headerFilter' => 'input',
                    'minWidth' => 160,
                ],
                [
                    'title' => 'Type',
                    'field' => 'type',
                    'sortField' => 'type',
                    'headerSort' => true,
                    'headerFilter' => 'input',
                    'minWidth' => 140,
                ],
                [
                    'title' => 'FCA',
                    'field' => 'fairchanceact',
                    'formatter' => 'yesNo',
                    'sortField' => 'fairchanceact',
                    'headerSort' => true,
                    'width' => 100,
                ],
                [
                    'title' => 'Habitable',
                    'field' => 'habitable',
                    'formatter' => 'yesNo',
                    'sortField' => 'habitable',
                    'headerSort' => true,
                    'width' => 120,
                ],
                [
                    'title' => 'Lat',
                    'field' => 'latitude',
                    'sorter' => 'number',
                    'sortField' => 'latitude',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 110,
                ],
                [
                    'title' => 'Lon',
                    'field' => 'longitude',
                    'sorter' => 'number',
                    'sortField' => 'longitude',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 110,
                ],
                [
                    'title' => 'Population',
                    'field' => 'sensor.population',
                    'sorter' => 'number',
                    'sortField' => 'sensor_population',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 140,
                ],
                [
                    'title' => 'Economy',
                    'field' => 'sensor.economy',
                    'sorter' => 'number',
                    'sortField' => 'sensor_economy',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 120,
                ],
                [
                    'title' => 'Danger',
                    'field' => 'sensor.danger',
                    'sorter' => 'number',
                    'sortField' => 'sensor_danger',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 120,
                ],
            ],
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">Starmap Celestial Objects</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
    </div>
@endsection
