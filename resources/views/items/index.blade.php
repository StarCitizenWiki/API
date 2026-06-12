@php use Illuminate\Support\Arr;use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen Items
@endsection
@section('meta_description')
    {!! data_get($seo, 'metaDescription', 'Items in all categories. Weapons, armor, components, gadgets, and more.') !!}
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
            'externalFilters' => $externalFilters,
            'columnBuilder' => $columnBuilder ?? false,
            'columnBuilderCoreFields' => $columnBuilderCoreFields ?? [],
            'fieldCatalog' => $fieldCatalog ?? [],
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
        />
        <x-column-source-map :columns="$tableColumns"/>
    </div>
@endsection

@push('scripts')
<script>
    const h1 = document.querySelector('h1[data-testid="items-index-heading"]');

    const setPageTitle = (title) => {
        if (!h1) {
            return;
        }

        if (typeof title === 'undefined' || title.length === 0) {
            h1.textContent = 'Items';
        } else {
            h1.textContent = `${title.map(val => val.replaceAll('_', ' ')).join(', ')} Items`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const url = new URL(window.location.href);
        const typeFilter = url.searchParams.get('filter[type]');

        if (!typeFilter) {
            return;
        }

        setPageTitle(typeFilter.split(','));
    });

    window.addEventListener('tabulator:ready', (event) => {
        const { id, table } = event.detail;

        if (id !== 'items-table' || !h1) {
            return;
        }

        table.on('dataFiltering', function (filterParams) {
            setPageTitle(filterParams.find(filter => filter.field === 'type')?.value);
        });
    });
</script>
@endpush
