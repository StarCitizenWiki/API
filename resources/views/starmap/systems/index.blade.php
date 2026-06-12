@extends('layouts.app')

@section('title', 'Starmap Systems')
@section('meta_description', 'Star systems in the Star Citizen starmap with celestial objects, jump points, and affiliations.')

@section('content')
    @php
        $tableId = 'starmap-systems-table';
        $tableConfig = [
            'endpoint' => route('starsystems.index'),
            'filterOptionsEndpoint' => route('starsystems.filters'),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => [
                'status' => 'status',
                'type' => 'type',
            ],
            'apiUrlTargetId' => 'starmap-systems-api-url',
            'columns' => [
                ['title' => 'ID', 'field' => 'id', 'sorter' => 'number', 'headerSort' => false, 'width' => 100],
                ['title' => 'Code', 'field' => 'code', 'headerSort' => true, 'headerFilter' => 'input', 'width' => 120],
                ['title' => 'Name', 'field' => 'name', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 200],
                [
                    'title' => 'Status',
                    'field' => 'status',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 140,
                ],
                [
                    'title' => 'Type',
                    'field' => 'type',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 140,
                ],
                ['title' => 'Planets', 'field' => 'aggregated.planets', 'sorter' => 'number', 'headerSort' => false, 'hozAlign' => 'right', 'width' => 110],
                ['title' => 'Moons', 'field' => 'aggregated.moons', 'sorter' => 'number', 'headerSort' => false, 'hozAlign' => 'right', 'width' => 100],
                [
                    'title' => 'Size',
                    'field' => 'aggregated.size',
                    'sorter' => 'number',
                    'sortField' => 'aggregated_size',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 110,
                ],
                [
                    'title' => 'Population',
                    'field' => 'aggregated.population',
                    'sorter' => 'number',
                    'sortField' => 'aggregated_population',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 130,
                ],
                [
                    'title' => 'Economy',
                    'field' => 'aggregated.economy',
                    'sorter' => 'number',
                    'sortField' => 'aggregated_economy',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 120,
                ],
                [
                    'title' => 'Danger',
                    'field' => 'aggregated.danger',
                    'sorter' => 'number',
                    'sortField' => 'aggregated_danger',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 110,
                ],
            ],
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">Starmap Systems</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
        />
    </div>
@endsection
