@php
    $activeFilterCount = count($selectedIngredientResourceTypeUuids);
@endphp

<div class="space-y-4">
    <div class="rounded-box border border-base-300 bg-base-200/40 p-4">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <label class="input input-bordered flex min-h-12 w-full items-center gap-2 bg-base-100 px-4">
                <x-icon name="search" class="size-4 text-base-content/55" />
                <span class="sr-only">Search craftable blueprints</span>
                <input
                    type="search"
                    class="w-full"
                    placeholder="Search craftable blueprints"
                    value="{{ $searchQuery }}"
                    data-blueprint-search
                    aria-label="Search craftable blueprints"
                />
            </label>

            <button
                type="button"
                class="{{ $activeFilterCount === 0
                    ? 'btn btn-outline min-h-12 justify-between border-base-300 bg-base-100 text-base-content'
                    : 'btn btn-outline btn-primary min-h-12 justify-between border-primary/40 bg-primary/5 text-primary' }}"
                data-resource-filter-toggle
                aria-expanded="false"
                aria-controls="blueprint-resource-filter-panel"
            >
                <span>Filter by resource</span>
                <span class="badge badge-ghost badge-sm" data-resource-filter-count>{{ $activeFilterCount }} selected</span>
            </button>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2" data-resource-filter-chips @if ($selectedIngredientResourceTypeUuids === []) hidden @endif></div>
    </div>

    <div
        id="blueprint-resource-filter-panel"
        class="rounded-box border border-base-300 bg-base-100 p-4 shadow-sm"
        data-resource-filter-panel
        hidden
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="text-sm font-semibold">Filter by resource</div>
                <p class="mt-1 text-xs leading-5 text-base-content/70">
                    Pick one or more input materials. Results only include blueprints that use every selected resource.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-ghost btn-xs hidden w-fit px-0 text-base-content/70 hover:bg-transparent hover:text-base-content"
                data-resource-filter-clear
            >
                Clear all
            </button>
        </div>

        <div class="mt-4 grid gap-2 sm:grid-cols-2 2xl:grid-cols-3" data-resource-filter-options>
            <div class="rounded-box border border-dashed border-base-300 bg-base-200/30 px-3 py-4 text-sm text-base-content/70">
                Loading resource filters...
            </div>
        </div>

        <div class="rounded-box border border-dashed border-base-300 bg-base-200/30 px-3 py-4 text-sm text-base-content/70" data-resource-filter-empty hidden>
            No blueprint input resources are available for this game version.
        </div>
    </div>

    <section class="space-y-3">
        <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
            <span>Matching blueprints</span>
            <span class="text-base-content/55" data-blueprint-search-count>
                ({{ $renderSearchResultCount }} result{{ $renderSearchResultCount === 1 ? '' : 's' }})
            </span>
        </div>

        <div class="grid gap-3" data-blueprint-search-results-list>
            @foreach ($initialSearchResults as $searchResult)
                @include('blueprints.partials.search-result')
            @endforeach
        </div>

        <div class="rounded-box border border-base-300 bg-base-100 px-4 py-3 text-sm text-base-content/70" data-blueprint-search-loading hidden>
            Searching blueprints...
        </div>

        <div
            class="rounded-box border border-dashed border-base-300 bg-base-100 px-4 py-5 text-sm text-base-content/70"
            data-blueprint-search-empty
            @if ($initialSearchResults !== []) hidden @endif
        >
            @if (! $hasSearchFilters)
                Search by output name or pick resource filters to load matching blueprints.
            @else
                No blueprints matched the current search and resource filters.
            @endif
        </div>
    </section>
</div>
