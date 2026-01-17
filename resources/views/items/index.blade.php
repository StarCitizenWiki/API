@extends('layouts.app')

@section('title', $pageTitle)
@section('meta_description', 'Browse Items.')

@section('content')
    @php
        $tableId = 'items-table';
        $resolvedVersionCode = $selectedGameVersionCode ?? session('game_version_code') ?? request()->query('version');
        $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];

        // Include endpoint filters (e.g. category) without forcing header filters
        $endpointParams = $versionParams;
        if (!empty($endpointFilters)) {
            $endpointParams['filter'] = $endpointFilters;
        }

        $tableConfig = [
            'endpoint' => route($endpointRouteName ?? 'items.index', $endpointParams),
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

        $filterQuery = request()->query('filter', []);
        $firstFilterValue = static function (mixed $value): ?string {
            if (is_array($value)) {
                foreach ($value as $entry) {
                    $trimmed = trim((string) $entry);
                    if ($trimmed !== '') {
                        $value = $trimmed;
                        break;
                    }
                }
            }

            if ($value === null) {
                return null;
            }

            $normalized = trim((string) $value);
            if ($normalized === '') {
                return null;
            }

            $parts = explode(',', $normalized);
            $firstPart = trim((string) $parts[0]);

            return $firstPart === '' ? null : $firstPart;
        };

        $typeFilter = $firstFilterValue(\Illuminate\Support\Arr::get($filterQuery, 'type'));
        $subTypeFilter = $firstFilterValue(\Illuminate\Support\Arr::get($filterQuery, 'sub_type'));
        $manufacturerFilter = $firstFilterValue(\Illuminate\Support\Arr::get($filterQuery, 'manufacturer.name'));

        $breadcrumbs = [
            [
                'label' => 'All Items',
                'url' => route('web.items.index', $versionParams),
            ],
        ];

        $filterStack = [];

        if ($typeFilter !== null) {
            $filterStack['type'] = $typeFilter;
            $breadcrumbs[] = [
                'label' => \Illuminate\Support\Str::headline($typeFilter),
                'url' => route('web.items.index', array_merge($versionParams, ['filter' => $filterStack])),
            ];
        }

        if ($subTypeFilter !== null) {
            $filterStack['sub_type'] = $subTypeFilter;
            $breadcrumbs[] = [
                'label' => \Illuminate\Support\Str::headline($subTypeFilter),
                'url' => route('web.items.index', array_merge($versionParams, ['filter' => $filterStack])),
            ];
        }

        if ($manufacturerFilter !== null) {
            $filterStack['manufacturer.name'] = $manufacturerFilter;
            $breadcrumbs[] = [
                'label' => \Illuminate\Support\Str::headline($manufacturerFilter),
                'url' => route('web.items.index', array_merge($versionParams, ['filter' => $filterStack])),
            ];
        }

    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">{{ $pageTitle }}</h1>
            <div class="breadcrumbs text-sm text-base-content/70">
                <ul>
                    @foreach ($breadcrumbs as $breadcrumb)
                        <li>
                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
        <x-column-source-map :columns="$tableColumns" />
    </div>
@endsection
