@extends('layouts.app')

@section('title')
    {{ $pageTitle }} - Star Citizen
@endsection
@section('meta_description', 'Browse Star Citizen blueprints.')

@section('content')
    @php
        $tableId = 'blueprints-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? request()->query('version') ?? session('game_version_code');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];

        $tableConfig = [
            'endpoint' => route('blueprints.index', $versionParams),
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
                <h1 class="text-2xl font-semibold tracking-tight">{{ $pageTitle }}</h1>
                <p class="text-sm text-base-content/70">Browse craftable blueprints for the selected game version.</p>
            </div>

            <a class="btn btn-primary sm:shrink-0" href="{{ route('web.blueprints.search', $versionParams) }}">
                Search Blueprints
            </a>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
        <x-column-source-map :columns="$tableColumns" />
    </div>
@endsection
