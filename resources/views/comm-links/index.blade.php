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
            'initialFilters' => $initialFilters ?? [],
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
                [
                    'title' => 'CIG ID', 'field' => 'id', 'sorter' => 'number', 'headerSort' => true, 'headerFilter' => 'input', 'width' => 100,
                    'formatter' => 'link',
                    'formatterParams' => [
                        'labelField' => 'id',
                        'target' => 'blank',
                        'urlField' => 'api_public_url',
                    ],
                ],
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
                    'field' => 'created_at_human',
                    'headerSort' => true,
                    'headerFilter' => 'input',
                    'headerFilterPlaceholder' => 'Year (e.g. 2024)',
                    'width' => 150,
                ],
                [
                    'title' => '',
                    'field' => 'id',
                    'formatter' => 'link',
                    'formatterParams' => [
                        'label' => 'API View',
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
            <h1 class="text-2xl font-semibold tracking-tight" data-testid="comm-links-index-heading">Comm-Links</h1>
        </div>

        @if (($searchType ?? null) === 'media-url')
            <div class="card border border-base-200 bg-base-100 shadow" data-testid="comm-links-media-url-results">
                <div class="card-body gap-3">
                    <div class="flex flex-col gap-1">
                        <h2 class="card-title">Media URL Results</h2>
                        @if (! empty($searchUrl))
                            <p class="text-sm text-base-content/70">{{ $searchUrl }}</p>
                        @endif
                    </div>

                    @if (! empty($searchCommLinks))
                        <div class="flex flex-col gap-2 text-sm">
                            @foreach ($searchCommLinks as $commLink)
                                <a class="link link-primary" href="{{ $commLink['url'] }}" data-testid="comm-links-media-url-result-{{ $commLink['id'] }}">
                                    {{ $commLink['id'] }} - {{ $commLink['title'] }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-base-content/70" data-testid="comm-links-media-url-empty-state">No comm-links found for that media URL.</p>
                    @endif
                </div>
            </div>
        @endif

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
    </div>
@endsection
