@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen Starmap
@endsection
@section('meta_description', 'Browse all Star Citizen starmap locations including planets, stations, outposts, and landing zones. Filter by system, type, amenities, and more.')

@section('meta')
    <x-seo.metadata
        :canonical="route('web.locations.index')"
        og-type="website"
        :og-title="$pageTitle.' - Star Citizen Starmap'"
        og-description="Browse all Star Citizen starmap locations including planets, stations, outposts, and landing zones."
        twitter-card="summary"
        :twitter-title="$pageTitle.' - Star Citizen Starmap'"
        twitter-description="Browse all Star Citizen starmap locations including planets, stations, outposts, and landing zones."
        :structured-data="[
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $pageTitle,
                'description' => 'Browse all Star Citizen starmap locations including planets, stations, outposts, and landing zones.',
                'url' => route('web.locations.index'),
                'about' => [
                    '@type' => 'Thing',
                    'name' => 'Star Citizen Starmap',
                ],
            ],
        ]"
    />
@endsection

@section('content')
    @php
        $tableId = 'starmap-locations-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];
        $tableConfig = [
            'endpoint' => route('locations.index', $versionParams),
            'filterOptionsEndpoint' => route('locations.filters', $versionParams),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'initialFilters' => $initialFilters,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => $headerFilterOptionsMap,
            'apiUrlTargetId' => 'starmap-locations-api-url',
            'columns' => $tableColumns,
            'externalFilters' => $externalFilters,
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight" data-testid="starmap-locations-index-heading">{{ $pageTitle }}</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
        <x-column-source-map :columns="$tableColumns" />
    </div>
@endsection
