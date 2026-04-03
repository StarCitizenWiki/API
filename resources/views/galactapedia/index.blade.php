@extends('layouts.app')

@section('title', 'Galactapedia')
@section('meta_description', 'Browse Galactapedia articles.')

@section('content')
    @php
        $tableId = 'galactapedia-table';
        $tableConfig = [
            'endpoint' => route('galactapedia.index'),
            'filterOptionsEndpoint' => route('galactapedia.filters'),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => [
                'category' => 'category',
                'tag' => 'tag',
                'template' => 'template',
            ],
            'apiUrlTargetId' => 'galactapedia-api-url',
            'columns' => [
                [
                    'title' => 'CIG ID', 'field' => 'id', 'sorter' => 'string', 'headerSort' => false, 'width' => 120,
                    'formatter' => 'link',
                    'formatterParams' => [
                        'labelField' => 'id',
                        'target' => 'blank',
                        'urlField' => 'web_url',
                    ],
                ],
                ['title' => 'Title', 'field' => 'title', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 260],
                ['title' => 'Type', 'field' => 'template', 'headerSort' => false, 'headerFilter' => 'list', 'minWidth' => 140],
                ['title' => 'Category', 'field' => 'category', 'headerSort' => false, 'headerFilter' => 'list', 'minWidth' => 200],
                ['title' => 'Tag', 'field' => 'tag', 'headerSort' => false, 'headerFilter' => 'list', 'minWidth' => 200],
                [
                    'title' => 'Related',
                    'field' => 'related_articles_count',
                    'sorter' => 'number',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 120,
                ],
                [
                    'title' => 'Publication',
                    'field' => 'created_at_human',
                    'headerSort' => true,
                    'headerFilter' => 'input',
                    'headerFilterPlaceholder' => 'Year (e.g. 2024)',
                    'width' => 150,
                ],
                [
                    'title' => 'API Url',
                    'field' => 'api_url',
                    'formatter' => 'link',
                    'formatterParams' => [
                        'label' => 'View',
                        'target' => 'blank',
                        'urlField' => 'api_url',
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
            <h1 class="text-2xl font-semibold tracking-tight">Galactapedia</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
    </div>
@endsection
