@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen
@endsection
@section('meta_description')
    {!! data_get($seo, 'metaDescription', 'Missions with payouts, objectives, faction details, and prerequisites.') !!}
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
        $tableId = 'missions-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];
        $endpoint = route('missions.index', $versionParams);
        if (! empty($locationFilter)) {
            $endpoint = url()->query($endpoint, ['filter[location]' => $locationFilter]);
        }
        $tableConfig = [
            'endpoint' => $endpoint,
            'filterOptionsEndpoint' => route('missions.filters', $versionParams),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'initialFilters' => $initialFilters,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => $headerFilterOptionsMap,
            'apiUrlTargetId' => 'missions-api-url',
            'columns' => $tableColumns,
            'externalFilters' => $externalFilters,
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight" data-testid="missions-index-heading">{{ $pageTitle }}</h1>
        </div>

        @if ($activeLocationFilter)
            <div class="flex items-center gap-2" data-testid="missions-location-filter-badge">
                <span class="text-sm text-subtle">Filtered by:</span>
                <a href="{{ route('web.missions.index', $versionParams) }}" class="badge badge-primary badge-sm gap-1.5" title="Clear location filter">
                    <x-icon name="map-pin" class="size-3" />
                    {{ $activeLocationFilter['name'] }}
                    <x-icon name="x" class="size-3 opacity-60" />
                </a>
            </div>
        @endif

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
        <x-column-source-map :columns="$tableColumns" />
    </div>
@endsection
