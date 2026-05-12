@php
    $activeFilterCount = count($selectedIngredientResourceTypeUuids);
@endphp

<div class="space-y-4">
    <div class="rounded-box border border-base-300 bg-base-200 p-4">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <label class="input input-bordered flex min-h-12 w-full items-center gap-2 bg-base-100 px-4">
                <x-icon name="search" class="size-4 text-muted" />
                <span class="sr-only">Search craftable blueprints</span>
                <input
                    x-ref="searchInput"
                    type="search"
                    class="w-full"
                    placeholder="Search craftable blueprints"
                    value="{{ $searchQuery }}"
                    data-testid="blueprints-search-input"
                    x-on:input="query = $event.target.value; onSearchInput()"
                    aria-label="Search craftable blueprints"
                    autocomplete="off"
                />
            </label>

            <button
                type="button"
                :class="filterToggleClass()"
                x-on:click="toggleFilter()"
                aria-expanded="false"
                aria-controls="blueprint-resource-filter-panel"
            >
                <span>Filter by resource</span>
                <span class="badge badge-soft badge-sm" x-text="selectedCount + ' selected'">{{ $activeFilterCount }} selected</span>
            </button>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2" x-show="selectedCount > 0" x-transition style="display: none;">
            <template x-for="uuid in selectedUuids" :key="uuid">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-2 text-xs font-medium text-primary"
                    x-on:click="removeFilterChip(uuid)"
                    x-text="(resourceTypes.find(r => r.uuid === uuid)?.name ?? uuid) + ' ×'"
                ></button>
            </template>
        </div>
    </div>

    <div
        id="blueprint-resource-filter-panel"
        class="rounded-box border border-base-300 bg-base-100 p-4 shadow-sm"
        x-show="filterOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 -translate-y-2"
        style="display: none;"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="text-sm font-semibold">Filter by resource</div>
                <p class="mt-1 text-xs leading-5 text-subtle">
                    Pick one or more input materials. Results only include blueprints that use every selected resource.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-ghost btn-xs w-fit px-0 text-subtle hover:bg-transparent hover:text-base-content"
                x-show="selectedCount > 0"
                x-on:click="clearAllFilters()"
                style="display: none;"
            >
                Clear all
            </button>
        </div>

        <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
            <template x-for="rt in resourceTypes" :key="rt.uuid">
                <label :class="filterOptionClass(rt.uuid)">
                    <input
                        type="checkbox"
                        class="checkbox checkbox-sm checkbox-primary mt-0.5"
                        :value="rt.uuid"
                        :checked="isResourceSelected(rt.uuid)"
                        x-on:change="toggleResourceSelection(rt.uuid)"
                    />
                    <span class="min-w-0">
                        <span class="block text-sm font-medium" x-text="rt.name || rt.key || 'Resource'"></span>
                    </span>
                </label>
            </template>

            {{-- Loading state --}}
            <template x-if="resourceTypes.length === 0 && !filterLoading">
                <div class="rounded-box border border-dashed border-base-300 bg-base-200 px-3 py-4 text-sm text-subtle">
                    No blueprint input resources are available for this game version.
                </div>
            </template>
        </div>
    </div>

    <section class="space-y-3">
        <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
            <span>Matching blueprints</span>
            <span class="text-muted" x-text="'(' + pluralizeResults(resultCount) + ')'">
                ({{ $renderSearchResultCount }} result{{ $renderSearchResultCount === 1 ? '' : 's' }})
            </span>
        </div>

        <div class="grid gap-3" data-testid="blueprints-search-results-list">
            <template x-for="(result, idx) in results" :key="result.uuid ?? idx">
                <a
                    :href="buildResultUrl(result)"
                    :class="resultCardClass(result)"
                    x-on:click="onResultClick(result, $event)"
                    data-blueprint-search-result-link
                    :data-blueprint-uuid="result.uuid"
                    :aria-current="isCurrentBlueprint(result) ? 'page' : null"
                >
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-base font-semibold text-base-content" x-text="result.output_name || 'Unknown blueprint'"></span>
                                <template x-if="isCurrentBlueprint(result)">
                                    <span class="badge badge-primary badge-sm">Selected</span>
                                </template>
                            </div>
                            <template x-if="result.output_class">
                                <p class="mt-1 truncate text-xs font-mono text-muted" x-text="result.output_class"></p>
                            </template>
                            <template x-if="ingredientPreview(result.ingredients)">
                                <p class="mt-2 text-xs text-subtle">
                                    <span class="font-semibold text-emphasis">Inputs:</span>
                                    <span x-text="ingredientPreview(result.ingredients)"></span>
                                </p>
                            </template>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                            <template x-if="resultTypeLabel(result)">
                                <span class="badge badge-soft badge-sm" x-text="resultTypeLabel(result)"></span>
                            </template>
                            <span class="badge badge-outline badge-sm" x-text="(result.ingredient_count || 0) + ' input' + ((result.ingredient_count || 0) === 1 ? '' : 's')"></span>
                            <template x-if="formatCraftTime(result.craft_time_seconds)">
                                <span class="badge badge-outline badge-sm" x-text="formatCraftTime(result.craft_time_seconds)"></span>
                            </template>
                        </div>
                    </div>
                </a>
            </template>
        </div>

        <div
            class="rounded-box border border-base-300 bg-base-100 px-4 py-3 text-sm text-subtle"
            x-show="loading"
            x-transition
            style="display: none;"
        >
            Searching blueprints...
        </div>

        <div
            class="rounded-box border border-dashed border-base-300 bg-base-100 px-4 py-5 text-sm text-subtle"
            data-testid="blueprints-search-empty-state"
            x-show="showEmpty"
            x-transition
            style="display: none;"
        >
            <span x-text="emptyMessage"></span>
        </div>
    </section>
</div>
