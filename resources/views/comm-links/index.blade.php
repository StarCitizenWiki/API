@extends('layouts.app')

@section('title', 'Comm-Links')
@section('meta_description', 'Browse Comm-Links.')

@section('content')
    @php
        $tableId = 'comm-links-table';
        $tableConfig = [
            'endpoint' => route('comm-links.index'),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => [
                'channel' => 'channel',
                'category' => 'category',
                'series' => 'series',
            ],
            'apiUrlTargetId' => 'comm-links-api-url',
            'columns' => [
                ['title' => 'CIG ID', 'field' => 'id', 'sorter' => 'number', 'headerSort' => true, 'headerFilter' => 'input', 'width' => 100],
                ['title' => 'Title', 'field' => 'title', 'headerSort' => true, 'headerFilter' => 'input', 'minWidth' => 260],
                ['title' => 'Images', 'field' => 'images_count', 'hozAlign' => 'right', 'headerSort' => true, 'width' => 100],
                ['title' => 'Links', 'field' => 'links_count', 'hozAlign' => 'right', 'headerSort' => true, 'width' => 100],
                ['title' => 'Content', 'field' => 'translations', 'formatter' => 'yesNo', 'headerSort' => false, 'width' => 100],
                [
                    'title' => 'Channel',
                    'field' => 'channel',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'headerFilterParams' => ['values' => ['' => 'All']],
                ],
                [
                    'title' => 'Category',
                    'field' => 'category',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'headerFilterParams' => ['values' => ['' => 'All']],
                ],
                [
                    'title' => 'Series',
                    'field' => 'series',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'headerFilterParams' => ['values' => ['' => 'All']],
                ],
                [
                    'title' => 'Publication',
                    'field' => 'created_at',
                    'headerSort' => true,
                    'headerFilter' => 'input',
                    'headerFilterPlaceholder' => 'Year (e.g. 2024)',
                ],
                [
                    'title' => '',
                    'field' => 'id',
                    'formatter' => 'viewButton',
                    'formatterParams' => [
                        'label' => 'View',
                        'hrefField' => 'api_public_url',
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
            <h1 class="text-2xl font-semibold tracking-tight">Comm-Links</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
    </div>
@endsection
