@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen
@endsection
@section('meta_description', 'Browse Star Citizen commodities and trade resources. Find prices, availability, and mining data across all tradeable goods.')

@section('meta')
    <x-seo.metadata
        :canonical="route('web.commodities.index')"
        og-type="website"
        :og-title="$pageTitle.' - Star Citizen Commodities'"
        og-description="Browse Star Citizen commodities and trade resources. Find prices, availability, and mining data."
        twitter-card="summary"
        :twitter-title="$pageTitle.' - Star Citizen Commodities'"
        twitter-description="Browse Star Citizen commodities and trade resources. Find prices, availability, and mining data."
        :structured-data="[
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $pageTitle,
                'description' => 'Browse Star Citizen commodities and trade resources.',
                'url' => route('web.commodities.index'),
                'about' => [
                    '@type' => 'CommodityType',
                    'name' => 'Star Citizen Commodities',
                ],
            ],
        ]"
    />
@endsection

@section('content')
    @php
        $tableId = 'resources-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];
        $tableConfig = [
            'endpoint' => route('commodities.index', $versionParams),
            'filterOptionsEndpoint' => route('commodities.filters', $versionParams),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'initialFilters' => $initialFilters,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => $headerFilterOptionsMap,
            'apiUrlTargetId' => 'resources-api-url',
            'columns' => $tableColumns,
            'externalFilters' => $externalFilters,
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight" data-testid="resources-index-heading">{{ $pageTitle }}</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
        <x-column-source-map :columns="$tableColumns" />
    </div>
@endsection
