@extends('layouts.app')

@section('title')
    {!! $pageTitleDecoded !!}@if (! $isEmptyMode) Blueprint @endif - Star Citizen
@endsection

@push('scripts')
    <script>
        (() => {
            const page = document.querySelector("[data-blueprint-show]");
            const payloadElement = document.getElementById("blueprint-show-data");

            if (!(page instanceof HTMLElement) || !payloadElement?.textContent) {
                return;
            }

            let payload;

            try {
                payload = JSON.parse(payloadElement.textContent);
            } catch {
                return;
            }

            const queryAll = (selector, scope = page) => [...scope.querySelectorAll(selector)];
            const setHidden = (element, hidden) => {
                if (element instanceof HTMLElement) {
                    element.hidden = hidden;
                }
            };
            const stringValue = (value, fallback = "") => {
                return typeof value === "string" && value !== "" ? value : fallback;
            };
            const numberValue = (value, fallback = 0) => {
                const numericValue = Number(value);

                return Number.isFinite(numericValue) ? numericValue : fallback;
            };
            const stringValues = (value) => {
                if (!Array.isArray(value)) {
                    return [];
                }

                return value
                    .map((item) => stringValue(item))
                    .filter((item, index, items) => item !== "" && items.indexOf(item) === index);
            };
            const escapeHtml = (value) => {
                return String(value)
                    .replaceAll("&", "&amp;")
                    .replaceAll("<", "&lt;")
                    .replaceAll(">", "&gt;")
                    .replaceAll('"', "&quot;")
                    .replaceAll("'", "&#39;");
            };
            const pluralizeResults = (count) => `${count} result${count === 1 ? "" : "s"}`;
            const ingredientNames = (ingredients) => {
                if (!Array.isArray(ingredients)) {
                    return [];
                }

                return ingredients
                    .map((ingredient) => stringValue(ingredient?.name))
                    .filter((item, index, items) => item !== "" && items.indexOf(item) === index);
            };
            const formatIngredientPreview = (ingredients) => {
                const allIngredientNames = ingredientNames(ingredients);
                const visibleIngredientNames = allIngredientNames.slice(0, 3);

                if (visibleIngredientNames.length === 0) {
                    return "";
                }

                const hiddenIngredientCount = allIngredientNames.length - visibleIngredientNames.length;

                return hiddenIngredientCount > 0
                    ? `${visibleIngredientNames.join(", ")} +${hiddenIngredientCount} more`
                    : visibleIngredientNames.join(", ");
            };
            const formatCraftTimeLabel = (value) => {
                const seconds = numberValue(value, -1);

                if (seconds < 0) {
                    return null;
                }

                if (seconds <= 60) {
                    return `${seconds} second${seconds === 1 ? "" : "s"}`;
                }

                if (seconds < 3600) {
                    const minutes = Math.floor(seconds / 60);
                    const remainder = seconds % 60;

                    if (remainder === 0) {
                        return `${minutes} minute${minutes === 1 ? "" : "s"}`;
                    }

                    return `${minutes} minute${minutes === 1 ? "" : "s"} ${remainder} second${remainder === 1 ? "" : "s"}`;
                }

                const hours = Math.floor(seconds / 3600);
                const remainingMinutes = Math.floor((seconds % 3600) / 60);

                if (remainingMinutes === 0) {
                    return `${hours} hour${hours === 1 ? "" : "s"}`;
                }

                return `${hours} hour${hours === 1 ? "" : "s"} ${remainingMinutes} minute${remainingMinutes === 1 ? "" : "s"}`;
            };
            const buildUrl = (endpoint, version) => {
                const url = new URL(endpoint, window.location.origin);

                if (version !== "" && !url.searchParams.has("version")) {
                    url.searchParams.set("version", version);
                }

                return url;
            };
            const interpolateModifier = (modifier, quality) => {
                const qualityMin = numberValue(modifier?.quality_range?.min, 0);
                const qualityMax = numberValue(modifier?.quality_range?.max, 1000);
                const modifierAtMinQuality = numberValue(modifier?.modifier_range?.at_min_quality, 1);
                const modifierAtMaxQuality = numberValue(
                    modifier?.modifier_range?.at_max_quality,
                    modifierAtMinQuality,
                );

                if (qualityMax === qualityMin) {
                    return modifierAtMaxQuality;
                }

                const ratio = (quality - qualityMin) / (qualityMax - qualityMin);

                return modifierAtMinQuality + ((modifierAtMaxQuality - modifierAtMinQuality) * ratio);
            };
            const relativeChange = (baselineValue, currentValue) => {
                if (baselineValue === 0) {
                    return currentValue === 0 ? 1 : currentValue;
                }

                return currentValue / baselineValue;
            };
            const isNeutral = (value) => Math.abs(value - 1) < 0.0005;
            const isImprovement = (modifier, value) => {
                if (modifier?.better_when === "lower") {
                    return value < 1;
                }

                if (modifier?.better_when === "higher") {
                    return value > 1;
                }

                return false;
            };
            const formatSemanticChange = (modifier, value) => {
                if (isNeutral(value)) {
                    return "No change";
                }

                const change = `${Math.abs((value - 1) * 100).toFixed(1)}%`;

                if (modifier?.better_when === "neutral") {
                    return `${change} changed`;
                }

                return `${change} ${isImprovement(modifier, value) ? "better" : "worse"}`;
            };
            const toneClasses = (modifier, value) => {
                if (isNeutral(value) || modifier?.better_when === "neutral") {
                    return {
                        text: "text-base-content/60",
                        card: "rounded-box border border-base-300 bg-base-100 px-3 py-3",
                    };
                }

                return isImprovement(modifier, value)
                    ? {
                        text: "text-success",
                        card: "rounded-box border border-success/25 bg-success/10 px-3 py-3",
                    }
                    : {
                        text: "text-error",
                        card: "rounded-box border border-error/25 bg-error/10 px-3 py-3",
                    };
            };

            const initSearchPanel = (search = {}) => {
                const searchInput = page.querySelector("[data-blueprint-search]");
                const searchResultsList = page.querySelector("[data-blueprint-search-results-list]");

                if (!(searchResultsList instanceof HTMLElement)) {
                    return;
                }

                const searchCountElements = queryAll("[data-blueprint-search-count]");
                const searchLoading = page.querySelector("[data-blueprint-search-loading]");
                const searchEmpty = page.querySelector("[data-blueprint-search-empty]");
                const resourceFilterToggle = page.querySelector("[data-resource-filter-toggle]");
                const resourceFilterCount = page.querySelector("[data-resource-filter-count]");
                const resourceFilterPanel = page.querySelector("[data-resource-filter-panel]");
                const resourceFilterClear = page.querySelector("[data-resource-filter-clear]");
                const resourceFilterChips = page.querySelector("[data-resource-filter-chips]");
                const resourceFilterOptions = page.querySelector("[data-resource-filter-options]");
                const resourceFilterEmpty = page.querySelector("[data-resource-filter-empty]");
                const state = {
                    activeController: null,
                    currentBlueprintUuid: stringValue(search.currentBlueprintUuid) || null,
                    debounceTimer: null,
                    initialResultCount: numberValue(search.initialResultCount, Array.isArray(search.initialResults) ? search.initialResults.length : 0),
                    initialResults: Array.isArray(search.initialResults) ? search.initialResults : [],
                    selectedResourceTypeUuids: new Set(
                        Array.isArray(search.selectedResourceTypeUuids) ? search.selectedResourceTypeUuids : [],
                    ),
                    version: stringValue(search.version),
                };

                const searchResultCardClass = (isSelected) => {
                    return isSelected
                        ? "group rounded-box border border-primary/40 bg-primary/5 px-4 py-4 shadow-sm transition hover:border-primary/50 hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
                        : "group rounded-box border border-base-300 bg-base-100 px-4 py-4 shadow-sm transition hover:border-primary/40 hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30";
                };
                const resourceFilterToggleClass = (selectedCount) => {
                    return selectedCount === 0
                        ? "btn btn-outline min-h-12 justify-between border-base-300 bg-base-100 text-base-content"
                        : "btn btn-outline btn-primary min-h-12 justify-between border-primary/40 bg-primary/5 text-primary";
                };
                const resourceFilterOptionCardClass = (isSelected) => {
                    return isSelected
                        ? "flex cursor-pointer items-start gap-3 rounded-box border border-primary/40 bg-primary/5 px-3 py-3 transition-colors"
                        : "flex cursor-pointer items-start gap-3 rounded-box border border-base-300 bg-base-100 px-3 py-3 transition-colors hover:border-primary/30 hover:bg-primary/5";
                };
                const optionInputs = () => {
                    if (!(resourceFilterOptions instanceof HTMLElement)) {
                        return [];
                    }

                    return queryAll("[data-resource-filter-option]", resourceFilterOptions).filter((element) => {
                        return element instanceof HTMLInputElement;
                    });
                };
                const selectedOptions = () => {
                    return optionInputs().filter((option) => state.selectedResourceTypeUuids.has(option.value));
                };
                const currentSearchQuery = () => {
                    return searchInput instanceof HTMLInputElement ? searchInput.value.trim() : "";
                };
                const selectedResourceTypeUuidValues = () => {
                    return [...state.selectedResourceTypeUuids];
                };
                const applySearchRequestFilters = (url, query, selectedResourceTypeUuids) => {
                    url.searchParams.delete("filter[query]");
                    url.searchParams.delete("filter[ingredient.uuid]");

                    if (query !== "") {
                        url.searchParams.set("filter[query]", query);
                    }

                    if (selectedResourceTypeUuids.length > 0) {
                        url.searchParams.set("filter[ingredient.uuid]", selectedResourceTypeUuids.join(","));
                    }

                    return url;
                };
                const applySearchResultFilters = (url, selectedResourceTypeUuids) => {
                    url.searchParams.delete("filter[query]");
                    url.searchParams.delete("filter[ingredient.uuid]");

                    if (selectedResourceTypeUuids.length > 0) {
                        url.searchParams.set("filter[ingredient.uuid]", selectedResourceTypeUuids.join(","));
                    }

                    return url;
                };
                const buildSearchResultUrl = (result) => {
                    const endpoint = stringValue(result?.web_url) || stringValue(result?.link);

                    if (endpoint === "") {
                        return "";
                    }

                    return applySearchResultFilters(
                        buildUrl(endpoint, state.version),
                        selectedResourceTypeUuidValues(),
                    ).toString();
                };
                const setSearchCount = (count) => {
                    searchCountElements.forEach((element) => {
                        if (!(element instanceof HTMLElement)) {
                            return;
                        }

                        element.textContent = element.closest("summary") instanceof HTMLElement
                            ? pluralizeResults(count)
                            : `(${pluralizeResults(count)})`;
                    });
                };
                const searchEmptyMessage = () => {
                    const query = searchInput instanceof HTMLInputElement ? searchInput.value.trim() : "";

                    if (query === "" && state.selectedResourceTypeUuids.size === 0) {
                        return "Search by output name or pick resource filters to load matching blueprints.";
                    }

                    return "No blueprints matched the current search and resource filters.";
                };
                const renderSearchEmpty = (message) => {
                    if (!(searchEmpty instanceof HTMLElement)) {
                        return;
                    }

                    searchEmpty.hidden = false;
                    searchEmpty.textContent = message;
                };
                const renderSearchResult = (result) => {
                    const url = buildSearchResultUrl(result);

                    if (url === "") {
                        return "";
                    }

                    const resultUuid = stringValue(result?.uuid);
                    const isSelected = state.currentBlueprintUuid !== null && resultUuid === state.currentBlueprintUuid;
                    const title = stringValue(result?.output_name, "Unknown blueprint");
                    const outputClass = stringValue(result?.output_class);
                    const typeLabel = [stringValue(result?.output?.type), stringValue(result?.output?.subtype)]
                        .filter(Boolean)
                        .join(" / ");
                    const inputCount = numberValue(result?.ingredient_count, 0);
                    const ingredientPreview = formatIngredientPreview(result?.ingredients);
                    const craftTimeLabel = formatCraftTimeLabel(result?.craft_time_seconds);

                    return `
                        <a
                            href="${escapeHtml(url)}"
                            class="${searchResultCardClass(isSelected)}"
                            data-blueprint-search-result-link
                            data-blueprint-uuid="${escapeHtml(resultUuid)}"
                            ${isSelected ? 'aria-current="page"' : ""}
                        >
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-base font-semibold text-base-content">${escapeHtml(title)}</span>
                                        ${isSelected ? '<span class="badge badge-primary badge-sm">Selected</span>' : ""}
                                    </div>
                                    ${outputClass === "" ? "" : `<p class="mt-1 truncate text-xs font-mono text-base-content/55">${escapeHtml(outputClass)}</p>`}
                                    ${ingredientPreview === "" ? "" : `<p class="mt-2 text-xs text-base-content/70"><span class="font-semibold text-base-content/80">Inputs:</span> ${escapeHtml(ingredientPreview)}</p>`}
                                </div>
                                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                    ${typeLabel === "" ? "" : `<span class="badge badge-ghost badge-sm">${escapeHtml(typeLabel)}</span>`}
                                    <span class="badge badge-outline badge-sm">${inputCount} input${inputCount === 1 ? "" : "s"}</span>
                                    ${craftTimeLabel === null ? "" : `<span class="badge badge-outline badge-sm">${escapeHtml(craftTimeLabel)}</span>`}
                                </div>
                            </div>
                        </a>
                    `.trim();
                };
                const renderSearchResults = (results, count, message = null) => {
                    const cards = results
                        .map(renderSearchResult)
                        .filter((markup) => markup !== "");

                    searchResultsList.innerHTML = cards.join("");
                    setSearchCount(count);

                    if (cards.length === 0) {
                        renderSearchEmpty(message ?? searchEmptyMessage());

                        return;
                    }

                    setHidden(searchEmpty, true);
                };
                const renderResourceFilterOption = (resourceType) => {
                    const value = stringValue(resourceType?.uuid);
                    const label = stringValue(resourceType?.name, stringValue(resourceType?.key, "Resource"));
                    const isSelected = state.selectedResourceTypeUuids.has(value);

                    return `
                        <label class="${resourceFilterOptionCardClass(isSelected)}" data-resource-filter-option-card>
                            <input
                                type="checkbox"
                                class="checkbox checkbox-sm checkbox-primary mt-0.5"
                                value="${escapeHtml(value)}"
                                data-resource-filter-option
                                ${isSelected ? "checked" : ""}
                            />
                            <span class="min-w-0">
                                <span class="block text-sm font-medium" data-resource-filter-name>${escapeHtml(label)}</span>
                            </span>
                        </label>
                    `.trim();
                };
                const syncResourceFilterUi = () => {
                    const selectedCount = state.selectedResourceTypeUuids.size;

                    if (resourceFilterCount instanceof HTMLElement) {
                        resourceFilterCount.textContent = `${selectedCount} selected`;
                    }

                    if (resourceFilterToggle instanceof HTMLButtonElement) {
                        resourceFilterToggle.className = resourceFilterToggleClass(selectedCount);
                    }

                    if (resourceFilterClear instanceof HTMLButtonElement) {
                        resourceFilterClear.classList.toggle("hidden", selectedCount === 0);
                    }

                    optionInputs().forEach((option) => {
                        option.checked = state.selectedResourceTypeUuids.has(option.value);
                        option.closest("[data-resource-filter-option-card]")?.setAttribute(
                            "class",
                            resourceFilterOptionCardClass(option.checked),
                        );
                    });

                    if (!(resourceFilterChips instanceof HTMLElement)) {
                        return;
                    }

                    resourceFilterChips.hidden = selectedCount === 0;
                    resourceFilterChips.innerHTML = selectedOptions().map((option) => {
                        const label = option.closest("[data-resource-filter-option-card]")
                            ?.querySelector("[data-resource-filter-name]")
                            ?.textContent
                            ?.trim() ?? option.value;

                        return `
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-2 text-xs font-medium text-primary"
                                data-resource-filter-chip="${escapeHtml(option.value)}"
                            >
                                ${escapeHtml(label)}
                                <span aria-hidden="true">×</span>
                            </button>
                        `.trim();
                    }).join("");
                };
                const setFilterSelection = (value, isSelected) => {
                    if (isSelected) {
                        state.selectedResourceTypeUuids.add(value);
                    } else {
                        state.selectedResourceTypeUuids.delete(value);
                    }

                    syncResourceFilterUi();
                    scheduleSearch();
                };
                const loadResourceFilters = async () => {
                    if (!(resourceFilterOptions instanceof HTMLElement)) {
                        return;
                    }

                    const endpoint = stringValue(search.resourceTypesEndpoint);

                    if (endpoint === "") {
                        resourceFilterOptions.innerHTML = "";

                        return;
                    }

                    try {
                        const response = await fetch(buildUrl(endpoint, state.version), {
                            headers: {
                                Accept: "application/json",
                                "X-Requested-With": "XMLHttpRequest",
                            },
                        });

                        if (!response.ok) {
                            throw new Error("Unable to load resource filters.");
                        }

                        const body = await response.json();
                        const resourceTypes = Array.isArray(body?.data) ? body.data : [];

                        resourceFilterOptions.innerHTML = resourceTypes.map(renderResourceFilterOption).join("");
                        setHidden(resourceFilterEmpty, resourceTypes.length !== 0);
                        syncResourceFilterUi();
                    } catch {
                        resourceFilterOptions.innerHTML = '<div class="rounded-box border border-dashed border-base-300 bg-base-200/30 px-3 py-4 text-sm text-base-content/70">Unable to load resource filters.</div>';
                        setHidden(resourceFilterEmpty, true);
                    }
                };
                const performSearch = async () => {
                    const query = currentSearchQuery();
                    const selectedResourceTypeUuids = selectedResourceTypeUuidValues();

                    if (query === "" && selectedResourceTypeUuids.length === 0) {
                        state.activeController?.abort();
                        state.activeController = null;
                        setHidden(searchLoading, true);
                        renderSearchResults(state.initialResults, state.initialResultCount);

                        return;
                    }

                    const endpoint = stringValue(search.apiEndpoint);

                    if (endpoint === "") {
                        renderSearchResults([], 0, "Unable to load blueprint results right now.");

                        return;
                    }

                    state.activeController?.abort();

                    const controller = new AbortController();

                    state.activeController = controller;
                    setHidden(searchLoading, false);

                    try {
                        const url = applySearchRequestFilters(
                            buildUrl(endpoint, state.version),
                            query,
                            selectedResourceTypeUuids,
                        );

                        const response = await fetch(url, {
                            headers: {
                                Accept: "application/json",
                                "X-Requested-With": "XMLHttpRequest",
                            },
                            signal: controller.signal,
                        });

                        if (!response.ok) {
                            throw new Error("Unable to load blueprints.");
                        }

                        const body = await response.json();

                        if (state.activeController !== controller || controller.signal.aborted) {
                            return;
                        }

                        renderSearchResults(
                            Array.isArray(body?.data) ? body.data : [],
                            numberValue(body?.meta?.total, Array.isArray(body?.data) ? body.data.length : 0),
                        );
                    } catch (error) {
                        if (error instanceof DOMException && error.name === "AbortError") {
                            return;
                        }

                        renderSearchResults([], 0, "Unable to load blueprint results right now.");
                    } finally {
                        if (state.activeController === controller) {
                            state.activeController = null;
                            setHidden(searchLoading, true);
                        }
                    }
                };
                const scheduleSearch = () => {
                    if (state.debounceTimer !== null) {
                        window.clearTimeout(state.debounceTimer);
                    }

                    state.debounceTimer = window.setTimeout(performSearch, 180);
                };

                searchInput?.addEventListener("input", scheduleSearch);

                page.addEventListener("change", (event) => {
                    const target = event.target;

                    if (!(target instanceof HTMLInputElement) || target.dataset.resourceFilterOption === undefined) {
                        return;
                    }

                    setFilterSelection(target.value, target.checked);
                });

                page.addEventListener("click", (event) => {
                    const target = event.target;

                    if (!(target instanceof HTMLElement)) {
                        return;
                    }

                    const filterToggle = target.closest("[data-resource-filter-toggle]");

                    if (filterToggle instanceof HTMLButtonElement) {
                        const isExpanded = filterToggle.getAttribute("aria-expanded") === "true";

                        filterToggle.setAttribute("aria-expanded", isExpanded ? "false" : "true");
                        setHidden(resourceFilterPanel, isExpanded);

                        return;
                    }

                    const clearButton = target.closest("[data-resource-filter-clear]");

                    if (clearButton instanceof HTMLButtonElement) {
                        state.selectedResourceTypeUuids.clear();
                        syncResourceFilterUi();
                        scheduleSearch();

                        return;
                    }

                    const chip = target.closest("[data-resource-filter-chip]");

                    if (chip instanceof HTMLButtonElement && typeof chip.dataset.resourceFilterChip === "string") {
                        setFilterSelection(chip.dataset.resourceFilterChip, false);

                        return;
                    }

                    const resultLink = target.closest("[data-blueprint-search-result-link]");

                    if (
                        resultLink instanceof HTMLAnchorElement
                        && state.currentBlueprintUuid !== null
                        && resultLink.dataset.blueprintUuid === state.currentBlueprintUuid
                    ) {
                        event.preventDefault();
                        document.getElementById("blueprint-recipe-flow")?.scrollIntoView({
                            behavior: "smooth",
                            block: "start",
                        });
                    }
                });

                renderSearchResults(state.initialResults, state.initialResultCount);
                syncResourceFilterUi();
                loadResourceFilters();
            };

            const initDetailTuning = (detail) => {
                if (!detail || !Array.isArray(detail.aspects)) {
                    return;
                }

                const aspects = detail.aspects;
                const qualityByAspect = aspects.map((aspect) => numberValue(aspect?.initial_quality, 500));
                const selectedByAspect = aspects.map((aspect) => aspect?.is_selected !== false);
                const aggregateEmpty = page.querySelector("[data-aggregate-empty]");
                const aggregateEmptyTitle = page.querySelector("[data-aggregate-empty-title]");
                const aggregateEmptyCopy = page.querySelector("[data-aggregate-empty-copy]");
                const bomEmptyRow = page.querySelector("[data-bom-empty-row]");
                const aggregateChangeElements = new Map(
                    queryAll("[data-aggregate-change]").map((element) => [
                        element.getAttribute("data-aggregate-change"),
                        element,
                    ]),
                );
                const aggregateCardElements = new Map(
                    queryAll("[data-aggregate-card]").map((element) => [
                        element.getAttribute("data-aggregate-card"),
                        element,
                    ]),
                );
                const aspectViews = aspects.map((aspect, aspectIndex) => {
                    const modifiers = Array.isArray(aspect?.modifiers) ? aspect.modifiers : [];

                    return {
                        card: page.querySelector(`[data-aspect-card="${aspectIndex}"]`),
                        modifierViews: modifiers.map((modifier, modifierIndex) => ({
                            card: page.querySelector(`[data-modifier-card="${aspectIndex}:${modifierIndex}"]`),
                            change: page.querySelector(`[data-modifier-change="${aspectIndex}:${modifierIndex}"]`),
                        })),
                        qualityValues: queryAll(`[data-aspect-quality-value="${aspectIndex}"]`),
                        reset: page.querySelector(`[data-aspect-reset="${aspectIndex}"]`),
                        sliders: queryAll(`[data-aspect-slider="${aspectIndex}"]`),
                        toggle: page.querySelector(`[data-aspect-toggle="${aspectIndex}"]`),
                        bomQualities: queryAll(`[data-bom-quality="${aspectIndex}"]`),
                    };
                });
                const selectionGroups = new Map();
                const selectionGroupViews = new Map();
                const activeAspectCardClass = "card border border-base-300 bg-base-200/60 shadow-sm";
                const inactiveAspectCardClass = "card border border-dashed border-base-300 bg-base-100/70 opacity-70 shadow-sm";
                const activeSelectionButtonClass = "btn btn-primary btn-xs";
                const inactiveSelectionButtonClass = "btn btn-outline btn-xs border-base-300 bg-base-100 text-base-content/70";
                const selectedCountForGroup = (group) => {
                    return group.aspectIndexes.filter((aspectIndex) => selectedByAspect[aspectIndex]).length;
                };
                const selectionGroupsAreComplete = () => {
                    return [...selectionGroups.values()].every((group) => {
                        return selectedCountForGroup(group) === group.requiredCount;
                    });
                };
                const setAggregateEmptyState = (title, copy) => {
                    setHidden(aggregateEmpty, false);

                    if (aggregateEmptyTitle instanceof HTMLElement) {
                        aggregateEmptyTitle.textContent = title;
                    }

                    if (aggregateEmptyCopy instanceof HTMLElement) {
                        aggregateEmptyCopy.textContent = copy;
                    }
                };
                const hideAggregateCards = () => {
                    aggregateCardElements.forEach((cardElement) => {
                        setHidden(cardElement, true);
                    });
                };
                const renderSelectionGroup = (groupKey) => {
                    const group = selectionGroups.get(groupKey);
                    const views = selectionGroupViews.get(groupKey);

                    if (!group || !views) {
                        return;
                    }

                    const selectedCount = selectedCountForGroup(group);
                    const warning = selectedCount === group.requiredCount
                        ? ""
                        : group.displayName !== ""
                            ? `Select ${group.requiredCount} of ${group.optionCount} options in ${group.displayName} to model a full recipe.`
                            : `Select ${group.requiredCount} of ${group.optionCount} options to model a full recipe.`;

                    views.counts.forEach((element) => {
                        if (!(element instanceof HTMLElement)) {
                            return;
                        }

                        element.textContent = `${selectedCount} of ${group.requiredCount} selected`;
                        element.className = selectedCount === group.requiredCount
                            ? "badge badge-ghost badge-sm"
                            : "badge badge-warning badge-sm";
                    });

                    views.warnings.forEach((element) => {
                        if (!(element instanceof HTMLElement)) {
                            return;
                        }

                        element.hidden = warning === "";
                        element.textContent = warning;
                    });
                };
                const renderSelectedInputsSummary = () => {
                    if (bomEmptyRow instanceof HTMLElement) {
                        bomEmptyRow.hidden = selectedByAspect.some(Boolean);
                    }
                };
                const renderAspect = (aspectIndex) => {
                    const aspect = aspects[aspectIndex];
                    const view = aspectViews[aspectIndex];

                    if (!aspect || !view) {
                        return;
                    }

                    const isSelected = selectedByAspect[aspectIndex] !== false;
                    const quality = qualityByAspect[aspectIndex];
                    const baselineQuality = numberValue(aspect.initial_quality, quality);
                    const modifiers = Array.isArray(aspect.modifiers) ? aspect.modifiers : [];

                    if (view.card instanceof HTMLElement) {
                        view.card.className = isSelected ? activeAspectCardClass : inactiveAspectCardClass;
                    }

                    if (view.toggle instanceof HTMLButtonElement) {
                        view.toggle.textContent = isSelected ? "Included" : "Excluded";
                        view.toggle.setAttribute("aria-pressed", isSelected ? "true" : "false");
                        view.toggle.className = isSelected ? activeSelectionButtonClass : inactiveSelectionButtonClass;
                    }

                    view.qualityValues.forEach((element) => {
                        if (element instanceof HTMLElement) {
                            element.textContent = isSelected ? String(quality) : "Off";
                        }
                    });

                    view.bomQualities.forEach((element) => {
                        if (!(element instanceof HTMLElement)) {
                            return;
                        }

                        const bomRow = element.closest("[data-bom-row]");

                        if (bomRow instanceof HTMLElement) {
                            bomRow.hidden = !isSelected;
                        }

                        element.textContent = isSelected ? `Q${quality}` : "Excluded";
                        element.className = isSelected ? "font-medium tabular-nums" : "text-base-content/60";
                    });

                    view.sliders.forEach((element) => {
                        if (!(element instanceof HTMLInputElement)) {
                            return;
                        }

                        element.disabled = !isSelected;
                        element.value = String(quality);
                        element.className = isSelected
                            ? "range range-primary range-sm mt-3 w-full"
                            : "range range-primary range-sm mt-3 w-full opacity-50";
                    });

                    if (view.reset instanceof HTMLButtonElement) {
                        const isDefaultQuality = !isSelected || quality === baselineQuality;

                        view.reset.disabled = isDefaultQuality;
                        view.reset.className = isDefaultQuality
                            ? "hidden btn btn-ghost btn-xs text-base-content/40 transition-colors"
                            : "btn btn-outline btn-primary btn-xs border-primary/40 bg-base-100 text-primary transition-colors hover:bg-primary/10";
                    }

                    modifiers.forEach((modifier, modifierIndex) => {
                        const modifierView = view.modifierViews[modifierIndex];

                        if (!modifierView) {
                            return;
                        }

                        if (!isSelected) {
                            if (modifierView.card instanceof HTMLElement) {
                                modifierView.card.className = "rounded-box border border-dashed border-base-300 bg-base-100 px-3 py-3 opacity-60 transition-colors";
                            }

                            if (modifierView.change instanceof HTMLElement) {
                                modifierView.change.textContent = "Excluded";
                                modifierView.change.className = "text-sm font-semibold tabular-nums text-base-content/45";
                            }

                            return;
                        }

                        const relativeValue = relativeChange(
                            interpolateModifier(modifier, baselineQuality),
                            interpolateModifier(modifier, quality),
                        );
                        const tone = toneClasses(modifier, relativeValue);

                        if (modifierView.card instanceof HTMLElement) {
                            modifierView.card.className = `${tone.card} transition-colors`;
                        }

                        if (modifierView.change instanceof HTMLElement) {
                            modifierView.change.textContent = formatSemanticChange(modifier, relativeValue);
                            modifierView.change.className = `text-sm font-semibold tabular-nums ${tone.text}`;
                        }
                    });
                };
                const renderAggregateSummary = () => {
                    if (!detail.hasInteractiveAspects) {
                        setAggregateEmptyState(
                            "No adjustable output tuning available",
                            "This blueprint does not expose quality-range modifier data for its recipe inputs.",
                        );
                        hideAggregateCards();

                        return;
                    }

                    if (!selectionGroupsAreComplete()) {
                        const firstIncompleteGroup = [...selectionGroups.values()].find((group) => {
                            return selectedCountForGroup(group) !== group.requiredCount;
                        });

                        setAggregateEmptyState(
                            "Selection incomplete",
                            firstIncompleteGroup
                                ? (
                                    firstIncompleteGroup.displayName !== ""
                                        ? `Select ${firstIncompleteGroup.requiredCount} of ${firstIncompleteGroup.optionCount} options in ${firstIncompleteGroup.displayName} to preview full output changes.`
                                        : `Select ${firstIncompleteGroup.requiredCount} of ${firstIncompleteGroup.optionCount} options to preview full output changes.`
                                )
                                : "Complete every grouped input selection to preview full output changes.",
                        );
                        hideAggregateCards();

                        return;
                    }

                    const aggregateBaselineValues = new Map();
                    const aggregateCurrentValues = new Map();
                    let visibleAggregateCount = 0;

                    aspects.forEach((aspect, aspectIndex) => {
                        if (!selectedByAspect[aspectIndex]) {
                            return;
                        }

                        const baselineQuality = numberValue(aspect.initial_quality, 500);
                        const currentQuality = qualityByAspect[aspectIndex];

                        (Array.isArray(aspect.modifiers) ? aspect.modifiers : []).forEach((modifier) => {
                            const key = stringValue(modifier?.property_key);

                            if (key === "") {
                                return;
                            }

                            aggregateBaselineValues.set(
                                key,
                                (aggregateBaselineValues.get(key) ?? 1) * interpolateModifier(modifier, baselineQuality),
                            );
                            aggregateCurrentValues.set(
                                key,
                                (aggregateCurrentValues.get(key) ?? 1) * interpolateModifier(modifier, currentQuality),
                            );
                        });
                    });

                    (Array.isArray(detail.summaryProperties) ? detail.summaryProperties : []).forEach((summaryProperty) => {
                        const key = stringValue(summaryProperty?.property_key);

                        if (key === "") {
                            return;
                        }

                        const baselineValue = aggregateBaselineValues.get(key);
                        const currentValue = aggregateCurrentValues.get(key);
                        const cardElement = aggregateCardElements.get(key);
                        const changeElement = aggregateChangeElements.get(key);

                        if (baselineValue === undefined || currentValue === undefined) {
                            setHidden(cardElement, true);

                            return;
                        }

                        const relativeValue = relativeChange(baselineValue, currentValue);
                        const tone = toneClasses(summaryProperty, relativeValue);
                        const summaryIsNeutral = isNeutral(relativeValue);

                        if (cardElement instanceof HTMLElement) {
                            cardElement.className = `${tone.card} transition-colors`;
                            cardElement.hidden = summaryIsNeutral;
                        }

                        if (changeElement instanceof HTMLElement) {
                            changeElement.textContent = formatSemanticChange(summaryProperty, relativeValue);
                            changeElement.className = `text-sm font-semibold tabular-nums ${tone.text}`;
                        }

                        if (!summaryIsNeutral) {
                            visibleAggregateCount += 1;
                        }
                    });

                    if (visibleAggregateCount === 0) {
                        setAggregateEmptyState(
                            "No output changes from baseline",
                            "Move any quality slider away from its baseline to preview tuning changes.",
                        );
                    }

                    setHidden(aggregateEmpty, visibleAggregateCount !== 0);
                };
                const setAspectQuality = (aspectIndex, nextQuality) => {
                    const aspect = aspects[aspectIndex];

                    if (!aspect) {
                        return;
                    }

                    const minQuality = numberValue(aspect.slider_min, 0);
                    const maxQuality = Math.max(numberValue(aspect.slider_max, 1000), minQuality);

                    qualityByAspect[aspectIndex] = Math.min(Math.max(nextQuality, minQuality), maxQuality);
                    renderAspect(aspectIndex);
                    renderAggregateSummary();
                };
                const setAspectSelected = (aspectIndex, nextSelected) => {
                    const aspect = aspects[aspectIndex];
                    const groupKey = stringValue(aspect?.selection_group?.key);
                    const group = selectionGroups.get(groupKey);

                    if (!aspect || !group) {
                        return;
                    }

                    const selectedCount = selectedCountForGroup(group);

                    if (nextSelected && !selectedByAspect[aspectIndex] && selectedCount >= group.requiredCount) {
                        return;
                    }

                    selectedByAspect[aspectIndex] = nextSelected;
                    group.aspectIndexes.forEach((groupAspectIndex) => {
                        renderAspect(groupAspectIndex);
                    });
                    renderSelectionGroup(group.key);
                    renderSelectedInputsSummary();
                    renderAggregateSummary();
                };

                aspects.forEach((aspect, aspectIndex) => {
                    const selectionGroup = aspect?.selection_group;
                    const key = stringValue(selectionGroup?.key);
                    const requiredCount = numberValue(selectionGroup?.required_count, 1);
                    const optionCount = numberValue(selectionGroup?.option_count, 1);

                    if (key === "" || optionCount <= requiredCount) {
                        return;
                    }

                    if (!selectionGroups.has(key)) {
                        selectionGroups.set(key, {
                            aspectIndexes: [],
                            displayName: stringValue(selectionGroup?.display_name),
                            key,
                            optionCount,
                            requiredCount,
                        });
                        selectionGroupViews.set(key, {
                            counts: queryAll(`[data-selection-group-count="${key}"]`),
                            warnings: queryAll(`[data-selection-group-warning="${key}"]`),
                        });
                    }

                    selectionGroups.get(key).aspectIndexes.push(aspectIndex);
                });

                page.addEventListener("input", (event) => {
                    const target = event.target;

                    if (!(target instanceof HTMLInputElement) || target.dataset.aspectSlider === undefined) {
                        return;
                    }

                    setAspectQuality(Number(target.dataset.aspectSlider), numberValue(target.value, 0));
                });

                page.addEventListener("click", (event) => {
                    const target = event.target;

                    if (!(target instanceof HTMLElement)) {
                        return;
                    }

                    const resetButton = target.closest("[data-aspect-reset]");

                    if (resetButton instanceof HTMLButtonElement) {
                        const aspectIndex = Number(resetButton.dataset.aspectReset);
                        const aspect = aspects[aspectIndex];

                        if (aspect) {
                            setAspectQuality(aspectIndex, numberValue(aspect.initial_quality, 500));
                        }

                        return;
                    }

                    const toggleButton = target.closest("[data-aspect-toggle]");

                    if (toggleButton instanceof HTMLButtonElement) {
                        const aspectIndex = Number(toggleButton.dataset.aspectToggle);

                        setAspectSelected(aspectIndex, !selectedByAspect[aspectIndex]);
                    }
                });

                aspects.forEach((aspect, aspectIndex) => {
                    renderAspect(aspectIndex);
                });

                selectionGroups.forEach((group) => {
                    renderSelectionGroup(group.key);
                });

                renderSelectedInputsSummary();
                renderAggregateSummary();
            };

            initSearchPanel(payload.search ?? {});
            initDetailTuning(payload.detail ?? null);
        })();
    </script>
@endpush

@section('meta_description')
    {{ $metaDescription }}
@endsection

@section('meta')
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($isEmptyMode)
        <meta name="robots" content="noindex,follow">
    @else
        <meta name="keywords" content="{{ $blueprintName }},{{ $outputType ?? '' }},{{ $outputClass ?? '' }},Blueprint,Star Citizen,SC">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
@endsection

@push('styles')
    <style>
        [data-blueprint-search-loading][hidden],
        [data-blueprint-search-empty][hidden],
        [data-resource-filter-panel][hidden],
        [data-resource-filter-empty][hidden],
        [data-resource-filter-chips][hidden] {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="flex flex-col gap-6" data-blueprint-show>
        @if ($isEmptyMode)
            <div class="card border border-base-300 bg-base-100 shadow">
                <div class="card-body gap-5">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="badge badge-primary badge-sm">Blueprint Search</span>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-3xl font-semibold tracking-tight" data-testid="blueprints-search-heading">Find craftable items</h1>
                    </div>

                    @include('blueprints.partials.search-panel')
                </div>
            </div>
        @else
            <div class="card border border-base-300 bg-base-100 shadow-sm">
                <div class="card-body p-0">
                    <details class="collapse collapse-arrow rounded-box border-0 bg-base-100/80" @if ($searchQuery !== '') open @endif>
                        <summary class="collapse-title min-h-0 py-4 pr-10">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-sm font-semibold">Change blueprint</h2>
                                    <p class="text-xs text-base-content/70">
                                        Search or filter another craftable output.
                                    </p>
                                </div>

                                <span class="badge badge-outline badge-sm" data-blueprint-search-count>
                                    {{ $renderSearchResultCount }} result{{ $renderSearchResultCount === 1 ? '' : 's' }}
                                </span>
                            </div>
                        </summary>

                        <div class="collapse-content border-t border-base-300 px-4 pb-4 pt-4">
                            @include('blueprints.partials.search-panel')
                        </div>
                    </details>
                </div>
            </div>
        @endif

        @if ($isEmptyMode)
            <div class="card border border-base-300 bg-base-100 shadow">
                <div class="card-body gap-3">
                    <h2 class="text-lg font-semibold tracking-tight">Crafting breakdown</h2>
                </div>
            </div>
        @else
            <div class="card border border-base-300 bg-base-100 shadow" id="blueprint-recipe-flow">
                <div class="card-body gap-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <h2 class="text-lg font-semibold tracking-tight">Blueprint inputs</h2>
                        </div>

                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="badge badge-outline badge-sm">{{ count($aspects) }} recipe input{{ count($aspects) === 1 ? '' : 's' }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-12">
                        <div class="order-2 grid gap-4 xl:order-1 xl:col-span-7">
                            @if ($aspects === [])
                                <div class="rounded-box border border-dashed border-base-300 bg-base-200/30 p-6 text-sm text-base-content/70">
                                    No recipe inputs were returned for this blueprint.
                                </div>
                            @endif

                            @foreach ($aspectGroups as $aspectGroup)
                                @if ($aspectGroup['is_choice_group'])
                                    <div class="rounded-box border border-base-300 bg-base-200/30 p-4">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="space-y-2">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="badge badge-primary badge-sm">Choose {{ $aspectGroup['required_count'] }} of {{ $aspectGroup['option_count'] }}</span>
                                                    <span class="badge badge-outline badge-sm">Input set</span>
                                                </div>
                                                @if (is_string($aspectGroup['display_name'] ?? null) && trim((string) $aspectGroup['display_name']) !== '')
                                                    <h3 class="text-base font-semibold">{{ $aspectGroup['display_name'] }}</h3>
                                                @endif
                                            </div>

                                            <span class="badge badge-ghost badge-sm" data-selection-group-count="{{ $aspectGroup['key'] }}">
                                                {{ $aspectGroup['selected_count'] }} of {{ $aspectGroup['required_count'] }} selected
                                            </span>
                                        </div>

                                        <p class="mt-2 text-xs text-base-content/70">
                                            Default preview uses the first {{ $aspectGroup['required_count'] }} option{{ $aspectGroup['required_count'] === 1 ? '' : 's' }}.
                                            Switch selections to model a different valid recipe.
                                        </p>

                                        <div
                                            class="mt-3 rounded-box border border-warning/30 bg-warning/10 px-3 py-2 text-xs text-warning"
                                            data-selection-group-warning="{{ $aspectGroup['key'] }}"
                                            hidden
                                        ></div>

                                        <div class="mt-4 grid gap-4">
                                            @foreach ($aspectGroup['aspects'] as $groupedAspect)
                                                @include('blueprints.partials.aspect-card', [
                                                    'aspect' => $groupedAspect,
                                                    'aspectIndex' => $groupedAspect['index'],
                                                ])
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    @foreach ($aspectGroup['aspects'] as $groupedAspect)
                                        @include('blueprints.partials.aspect-card', [
                                            'aspect' => $groupedAspect,
                                            'aspectIndex' => $groupedAspect['index'],
                                        ])
                                    @endforeach
                                @endif
                            @endforeach
                        </div>

                        <div class="order-1 card border border-primary/40 bg-primary/10 shadow-sm xl:order-2 xl:sticky xl:top-24 xl:col-span-5 xl:self-start">
                            <div class="card-body gap-5">
                                <div class="space-y-4">
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="badge badge-primary badge-sm">Output</span>
                                        @if ($outputGrade)
                                            <span class="badge badge-ghost badge-sm">Grade {{ $outputGrade }}</span>
                                        @endif
                                        <span class="ml-auto">
                                            @if ($isAvailableByDefault)
                                                <span class="badge badge-success badge-sm">Default</span>
                                            @else
                                                <span class="badge badge-outline badge-sm">Unlock required</span>
                                            @endif
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        <h3 class="text-3xl font-semibold tracking-tight">
                                            @if ($outputItemWebUrl)
                                                <a href="{{ $outputItemWebUrl }}" class="link link-hover link-primary">{{ $blueprintName }}</a>
                                            @else
                                                {{ $blueprintName }}
                                            @endif
                                        </h3>

                                        @if ($blueprintKey)
                                            <div class="text-xs font-mono text-base-content/55">{{ $blueprintKey }}</div>
                                        @endif

                                        <p class="text-sm text-base-content/80">
                                            {{ $outputType ?? 'Unknown type' }}@if ($outputSubtype) / {{ $outputSubtype }}@endif
                                        </p>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-box border border-base-300 bg-base-100 px-4 py-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-base-content/70">Craft time</div>
                                            <div class="mt-1 text-sm font-medium">
                                                {{ $craftTimeLabel ?? 'Unknown' }}
                                            </div>
                                        </div>
                                    </div>

                                    @if (! $isAvailableByDefault && $unlockSources !== [])
                                        <div class="rounded-box border border-base-300 bg-base-100 px-4 py-4">
                                            <div class="text-sm font-semibold text-base-content">Unlock source</div>
                                            <div class="mt-3 space-y-2">
                                                @foreach ($unlockSources as $unlockSource)
                                                    <div class="rounded-box border border-base-300 bg-base-200/40 px-3 py-3">
                                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                                            <div>
                                                                <div class="text-sm font-medium">{{ $unlockSource['label'] }}</div>
                                                                <div class="mt-1 text-xs text-base-content/70">{{ $unlockSource['type'] }}</div>
                                                            </div>

                                                            @if ($unlockSource['key'])
                                                                <div class="text-xs font-mono text-base-content/55">{{ $unlockSource['key'] }}</div>
                                                            @elseif ($unlockSource['uuid'])
                                                                <div class="text-xs font-mono text-base-content/55">{{ $unlockSource['uuid'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <h4 class="text-sm font-semibold">Output changes</h4>

                                <div class="rounded-box border border-dashed border-base-300 bg-base-100 px-4 py-4" data-aggregate-empty>
                                    <div class="text-sm font-medium text-base-content" data-aggregate-empty-title>
                                        {{ $hasInteractiveAspects ? 'No output changes from baseline' : 'No adjustable output tuning available' }}
                                    </div>
                                    <p class="mt-1 text-xs text-base-content/70" data-aggregate-empty-copy>
                                        {{ $hasInteractiveAspects
                                            ? 'Move any quality slider away from its baseline to preview tuning changes.'
                                            : 'This blueprint does not expose quality-range modifier data for its recipe inputs.' }}
                                    </p>
                                </div>

                                <div class="grid gap-3" data-aggregate-list>
                                    @foreach ($summaryPropertyList as $summaryProperty)
                                        <div
                                            class="rounded-box border border-base-300 bg-base-100 px-4 py-3 transition-colors"
                                            data-aggregate-card="{{ data_get($summaryProperty, 'property_key') }}"
                                            hidden
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-medium">
                                                        {{ data_get($summaryProperty, 'label', data_get($summaryProperty, 'property_key', 'Property')) }}
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-right">
                                                    <div class="text-sm font-semibold tabular-nums text-base-content/60" data-aggregate-change="{{ data_get($summaryProperty, 'property_key') }}">No change</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <p class="text-xs text-base-content/75">
                                    Changes are shown relative to each selected input&apos;s baseline quality.
                                </p>

                                @if ($aspects !== [])
                                    <div class="border-t border-primary/15 pt-3">
                                        <div class="overflow-hidden rounded-box border border-base-300/80 bg-base-100/70">
                                            <table class="table">
                                                <thead class="bg-base-200/40 text-xs uppercase tracking-wide text-base-content/55">
                                                    <tr>
                                                        <th>Input</th>
                                                        <th class="text-right">Quality</th>
                                                        <th class="text-right">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-xs">
                                                    @foreach ($aspects as $aspectIndex => $aspect)
                                                        @php
                                                            $selectedInputName = is_string(data_get($aspect, 'input.name')) && trim((string) data_get($aspect, 'input.name')) !== ''
                                                                ? trim((string) data_get($aspect, 'input.name'))
                                                                : 'Unknown input';
                                                            $selectedInputAmount = $formatAspectAmount($aspect);
                                                        @endphp
                                                        <tr data-bom-row="{{ $aspectIndex }}" @if (($aspect['is_selected'] ?? true) === false) hidden @endif>
                                                            <td class="py-2">
                                                                <div class="font-medium text-base-content/85">{{ $aspect['name'] }}</div>
                                                                <div class="text-[11px] text-base-content/60">{{ $selectedInputName }}</div>
                                                            </td>
                                                            <td class="py-2 text-right">
                                                                <span class="font-medium tabular-nums text-base-content/80" data-bom-quality="{{ $aspectIndex }}">
                                                                    {{ $formatAspectQuality($aspect) }}
                                                                </span>
                                                            </td>
                                                            <td class="py-2 text-right text-base-content/70">
                                                                {{ $selectedInputAmount ?? 'Unknown' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr data-bom-empty-row hidden>
                                                        <td colspan="3" class="py-2 text-xs text-base-content/60">
                                                            No inputs currently selected.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
                <summary class="collapse-title min-h-11 py-3 font-semibold">Technical details</summary>
                <div class="collapse-content pt-0">
                    <div class="grid gap-4 border-t border-base-300 pt-4 xl:grid-cols-3">
                        <section class="card border border-base-300 bg-base-100 shadow-sm">
                            <div class="card-body gap-4">
                                <h2 class="card-title text-base">Blueprint metadata</h2>

                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Blueprint UUID</dt>
                                        <dd class="mt-1 break-all font-mono">{{ $blueprintUuid }}</dd>
                                    </div>

                                    @if ($apiLink)
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">API route</dt>
                                            <dd class="mt-1 break-all font-mono">
                                                <a class="link link-hover" href="{{ $apiLink }}">{{ $apiLink }}</a>
                                            </dd>
                                        </div>
                                    @endif

                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Availability</dt>
                                        <dd class="mt-1">{{ $isAvailableByDefault ? 'Available by default' : 'Not available by default' }}</dd>
                                    </div>
                                </dl>

                                @if (! $isAvailableByDefault && $unlockSources === [])
                                    <div class="space-y-2">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Where to find blueprint</div>

                                        <p class="text-sm text-base-content/70">
                                            Unlock required, but no source location was returned for this blueprint.
                                        </p>
                                    </div>
                                @elseif ($rewardPools !== [])
                                    <div class="space-y-2">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Reward pools</div>

                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($rewardPools as $rewardPool)
                                                <span class="badge badge-outline badge-sm">{{ data_get($rewardPool, 'key', 'Unknown reward pool') }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="card border border-base-300 bg-base-100 shadow-sm xl:col-span-2">
                            <div class="card-body gap-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h2 class="card-title text-base">Requirement groups</h2>
                                    <span class="badge badge-ghost badge-sm">{{ is_array($requirementGroups) ? count($requirementGroups) : 0 }}</span>
                                </div>

                                @if (! is_array($requirementGroups) || $requirementGroups === [])
                                    <p class="text-sm text-base-content/70">No requirement groups were returned for this blueprint.</p>
                                @else
                                    <div class="space-y-4">
                                        @foreach ($requirementGroups as $group)
                                            <div class="rounded-box border border-base-300 bg-base-200/30 p-4">
                                                <div class="flex flex-wrap items-start justify-between gap-2">
                                                    <div>
                                                        <h3 class="text-base font-semibold">
                                                            {{ $resolveRequirementLabel(data_get($group, 'name'), data_get($group, 'key'), 'Requirement group') }}
                                                        </h3>
                                                        @if (data_get($group, 'key'))
                                                            <div class="mt-1 text-xs font-mono text-base-content/60">{{ data_get($group, 'key') }}</div>
                                                        @endif
                                                    </div>

                                                    @if (data_get($group, 'required_count') !== null)
                                                        <span class="badge badge-outline badge-sm">
                                                            {{ data_get($group, 'required_count') }} required
                                                        </span>
                                                    @endif
                                                </div>

                                                @if (data_get($group, 'modifiers', []) !== [])
                                                    <div class="mt-3 flex flex-wrap gap-2">
                                                        @foreach (data_get($group, 'modifiers', []) as $modifier)
                                                            <span class="badge badge-primary badge-outline badge-sm">
                                                                {{ data_get($modifier, 'label', data_get($modifier, 'property_key', 'Modifier')) }}
                                                                @if (data_get($modifier, 'better_when'))
                                                                    better when {{ data_get($modifier, 'better_when') }}
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <div class="mt-3 space-y-3">
                                                    @foreach (data_get($group, 'children', []) as $node)
                                                        @include('blueprints.partials.requirement-node', [
                                                            'node' => $node,
                                                            'resolvedVersionCode' => $resolvedVersionCode,
                                                        ])
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="card border border-base-300 bg-base-100 shadow-sm xl:col-span-3">
                            <div class="card-body gap-3">
                                <h2 class="card-title text-base">Raw Blueprint Payload</h2>
                                <pre class="overflow-x-auto rounded-box bg-base-200 p-4 text-xs">{{ $rawBlueprintJson }}</pre>
                            </div>
                        </section>
                    </div>
                </div>
            </details>
        @endif

        <script type="application/json" id="blueprint-show-data">
            {!! $clientPayload !!}
        </script>
    </div>
@endsection
