@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen
@endsection
@section('meta_description', 'Resource deposits by location. Find where to mine minerals, metals, and gases.')

@section('content')
    @php
        $tableId = 'resource-guide-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];
        $tableConfig = [
            'endpoint' => route('locations.resources.summary', $versionParams),
            'filterOptionsEndpoint' => route('locations.resources.summary.filters', $versionParams),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'initialFilters' => $initialFilters,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => $headerFilterOptionsMap,
            'apiUrlTargetId' => 'resource-guide-api-url',
            'columns' => $tableColumns,
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight" data-testid="resource-guide-index-heading">{{ $pageTitle }}</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
        <x-column-source-map :columns="$tableColumns" />
    </div>
@endsection
