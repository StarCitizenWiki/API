@extends('layouts.app')

@section('title', $pageTitle)
@section('meta_description', 'Browse Items.')

@section('content')
    @php
        $tableId = 'items-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];

        // Include type filter in endpoint if present
        $endpointParams = $versionParams;
        if (!empty($initialFilters)) {
            $endpointParams['filter'] = [];
            foreach ($initialFilters as $filter) {
                if (isset($filter['field']) && isset($filter['value'])) {
                    $endpointParams['filter'][$filter['field']] = $filter['value'];
                }
            }
        }

        $tableConfig = [
            'endpoint' => route('items.index', $endpointParams),
            'pageSize' => 25,
            'progressiveLoad' => 'scroll',
            'initialHeaderFilter' => $initialHeaderFilter,
            'initialFilters' => $initialFilters ?? null,
            'columnDefaults' => [
                'headerSortTristate' => true,
            ],
            'headerFilterOptionsMap' => $headerFilterOptionsMap,
            'apiUrlTargetId' => 'items-api-url',
            'columns' => $tableColumns,
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">{{ $pageTitle }}</h1>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
    </div>
@endsection
