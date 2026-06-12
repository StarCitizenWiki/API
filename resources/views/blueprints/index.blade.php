@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen
@endsection
@section('meta_description')
    {!! data_get($seo, 'metaDescription', 'Crafting blueprints with ingredient lists, craft times, and output details.') !!}
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
        $tableId = 'blueprints-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? request()->query('version') ?? session('game_version_code');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];

        $tableConfig = [
            'endpoint' => route('blueprints.index', $versionParams),
            'filterOptionsEndpoint' => route('blueprints.filters', $versionParams),
            'pageSize' => $pageSize,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'columnDefaults' => [
                'headerSort' => false,
            ],
            'headerFilterOptionsMap' => $headerFilterOptionsMap,
            'apiUrlTargetId' => 'blueprints-api-url',
            'columns' => $tableColumns,
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-semibold tracking-tight" data-testid="blueprints-index-heading">{{ $pageTitle }}</h1>
            </div>

            <a class="btn btn-primary sm:shrink-0" href="{{ route('web.blueprints.search', $versionParams) }}">
                Search Blueprints
            </a>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
        />
        <x-column-source-map :columns="$tableColumns" />
    </div>
@endsection
