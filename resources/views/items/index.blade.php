@php use Illuminate\Support\Arr;use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen Items
@endsection
@section('meta_description', 'Browse the complete Star Citizen items database - weapons, armor, gadgets, components, and more. Filter by type, grade, and size.')

@section('meta')
    <x-seo.metadata
        :canonical="route('web.items.index')"
        og-type="website"
        :og-title="$pageTitle.' - Star Citizen Items'"
        og-description="Browse the complete Star Citizen items database - weapons, armor, gadgets, components, and more."
        twitter-card="summary"
        :twitter-title="$pageTitle.' - Star Citizen Items'"
        twitter-description="Browse the complete Star Citizen items database - weapons, armor, gadgets, components, and more."
        :structured-data="[
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $pageTitle,
                'description' => 'Browse the complete Star Citizen items database - weapons, armor, gadgets, components, and more.',
                'url' => route('web.items.index'),
                'about' => [
                    '@type' => 'ItemType',
                    'name' => 'Star Citizen Items',
                ],
            ],
        ]"
    />
@endsection

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
            'filterOptionsEndpoint' => route('items.filters', $endpointParams),
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

        $typeFilter = $firstFilterValue(Arr::get($filterQuery, 'type'));
        $subTypeFilter = $firstFilterValue(Arr::get($filterQuery, 'sub_type'));
        $manufacturerFilter = $firstFilterValue(Arr::get($filterQuery, 'manufacturer.name'));

    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight" data-testid="items-index-heading">{{ $pageTitle }}</h1>
            <x-items.item-breadcrumbs :filterQuery="$filterQuery" :typeFilter="$typeFilter" :versionParams="$versionParams" />
        </div>

        <x-tabulator-table
            :id="$tableId"
            :config="$tableConfig"
            :initial="$initialTableData"
        />
        <x-column-source-map :columns="$tableColumns"/>
    </div>
@endsection
