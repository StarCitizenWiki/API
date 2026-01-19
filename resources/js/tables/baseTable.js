import {
    Tabulator,
    Module,
    FilterModule,
    AjaxModule,
    SortModule,
    PageModule,
    FormatModule,
    EditModule,
    FrozenColumnsModule,
    MoveColumnsModule,
    MoveRowsModule,
    ResizeColumnsModule,
    SelectRowModule
} from 'tabulator-tables';

/**
 * Keeps horizontal scroll position across re-renders (remote sort/filter/pagination).
 *
 * Enable/disable per table with:
 *   preserveHorizontalScroll: true/false
 */
class ScrollPositionModule extends Module {
    static moduleName = "scrollPosition";
    static moduleInitOrder = 1;

    lastScrollLeft = 0;

    constructor(table) {
        super(table);

        this.registerTableOption("preserveHorizontalScroll", true);
    }

    initialize() {
        if (!this.table.options.preserveHorizontalScroll) return;

        this.table.on("scrollHorizontal", (left) => {
            if (left > 0) {
                this.lastScrollLeft = left;
            }
        });

        this.table.on("renderComplete", () => {
            const holder = this.table.element?.querySelector?.(".tabulator-tableholder");
            if (!holder) {
                return;
            }

            // Defer a frame so Tabulator finishes its own scroll syncing
            requestAnimationFrame(() => {
                holder.scrollLeft = this.lastScrollLeft;
            });
        });
    }
}

Tabulator.registerModule([
    ScrollPositionModule,
    FilterModule,
    AjaxModule,
    SortModule,
    PageModule,
    FormatModule,
    EditModule,
    FrozenColumnsModule,
    MoveColumnsModule,
    MoveRowsModule,
    ResizeColumnsModule,
    SelectRowModule,
]);

const tabulatorTables = new Map();

export function getTabulatorTable(id) {
    return tabulatorTables.get(id);
}

function readJsonScript(id) {
    const el = document.getElementById(id);
    if (!el) return null;
    return JSON.parse(el.textContent || "null");
}

function get(obj, path, fallback = undefined) {
    if (!path) return fallback;
    return path.split(".").reduce((acc, k) => (acc && acc[k] != null ? acc[k] : undefined), obj) ?? fallback;
}

function mapFilterFieldToApiField(field) {
    if (!field) return field;
    return field === "created_at_human" ? "created_at" : field;
}

function mapApiFilterFieldToColumnField(apiField, columnFieldsSet) {
    if (!apiField) return apiField;

    if (apiField === "created_at" && columnFieldsSet?.has?.("created_at_human")) {
        return "created_at_human";
    }

    return apiField;
}

function collectColumnFields(columns, set = new Set()) {
    (columns ?? []).forEach((column) => {
        if (Array.isArray(column?.columns) && column.columns.length > 0) {
            collectColumnFields(column.columns, set);
        } else if (column?.field) {
            set.add(column.field);
        }
    });
    return set;
}

function collectManagedHeaderFilterApiFields(columns, set = new Set()) {
    (columns ?? []).forEach((column) => {
        if (Array.isArray(column?.columns) && column.columns.length > 0) {
            collectManagedHeaderFilterApiFields(column.columns, set);
            return;
        }

        if (!column?.field) return;

        if (column.headerFilter !== undefined && column.headerFilter !== false) {
            set.add(mapFilterFieldToApiField(column.field));
        }
    });

    return set;
}

function buildInverseSortFieldMap(sortFieldMap) {
    const inverse = {};
    Object.entries(sortFieldMap ?? {}).forEach(([columnField, apiField]) => {
        if (!apiField) return;
        if (inverse[apiField] == null) {
            inverse[apiField] = columnField;
        }
    });
    return inverse;
}

function parsePositiveInt(value) {
    if (value == null) return null;
    const n = Number(value);
    if (!Number.isFinite(n)) return null;
    const i = Math.trunc(n);
    return i > 0 ? i : null;
}

function parseJsonApiStateFromLocation({ columnFields, apiToColumnSortFieldMap }) {
    const pageUrl = new URL(window.location.href);

    // sort=a,-b
    const sortParam = pageUrl.searchParams.get("sort");
    const initialSort = [];
    if (sortParam) {
        for (const raw of sortParam.split(",").map(s => s.trim()).filter(Boolean)) {
            const desc = raw.startsWith("-");
            const apiField = desc ? raw.slice(1) : raw;

            const columnField = apiToColumnSortFieldMap?.[apiField] ?? apiField;

            if (columnFields.has(columnField)) {
                initialSort.push({
                    column: columnField,
                    dir: desc ? "desc" : "asc",
                });
            }
        }
    }

    // filters: filter[field]=value
    const initialHeaderFilter = [];
    for (const [key, value] of pageUrl.searchParams.entries()) {
        const match = key.match(/^filter\[([^\]]+)\]$/);
        if (!match) continue;

        const apiField = match[1];
        if (value == null || String(value).length === 0) continue;

        const columnField = mapApiFilterFieldToColumnField(apiField, columnFields);
        if (!columnFields.has(columnField)) continue;

        initialHeaderFilter.push({ field: columnField, value: String(value) });
    }

    // JSON:API pagination
    const paginationSize = parsePositiveInt(pageUrl.searchParams.get("page[size]"));
    const paginationInitialPage = parsePositiveInt(pageUrl.searchParams.get("page[number]"));

    return {
        initialSort: initialSort.length ? initialSort : null,
        initialHeaderFilter: initialHeaderFilter.length ? initialHeaderFilter : null,
        paginationSize,
        paginationInitialPage,
    };
}

// Convert Tabulator params -> JSON:API query string
function buildJsonApiUrl(baseUrl, params, defaults = {}) {
    const u = new URL(baseUrl, window.location.origin);

    // pagination
    const defaultPage = defaults.page ?? 1;
    const defaultSize = defaults.pageSize ?? null;

    if (params.page != null && Number(params.page) !== defaultPage) {
        u.searchParams.set("page[number]", String(params.page));
    } else {
        u.searchParams.delete("page[number]");
    }

    if (params.size != null && (defaultSize === null || Number(params.size) !== Number(defaultSize))) {
        u.searchParams.set("page[size]", String(params.size));
    } else {
        u.searchParams.delete("page[size]");
    }

    // sorting: sort=a,-b
    const sorters = params.sorters ?? params.sort ?? [];
    if (sorters.length) {
        const normalizedSorters = Array.isArray(sorters) ? sorters : [];
        const sort = normalizedSorters
            .map((sorter) => (sorter.dir === "desc" ? "-" : "") + sorter.field)
            .join(",");
        u.searchParams.set("sort", sort);
    } else {
        u.searchParams.delete("sort");
    }

    // filters: filter[field]=value (simple mapping)
    const filters = params.filter ?? params.filters ?? [];

    // Build a Set of API-field-names that are managed by Tabulator.
    // Important: this must include fields even when the current filter is cleared,
    // otherwise we'd "preserve" stale filter[...] params from the base URL.
    const managedDefaults = defaults.managedFilterFields
        ? Array.from(defaults.managedFilterFields)
        : [];
    const managedFields = new Set([
        ...managedDefaults,
        ...filters.map(f => mapFilterFieldToApiField(f?.field)).filter(Boolean),
    ]);

    // Preserve existing filter[...] keys that aren't managed by Tabulator
    const preservedFilters = new Map();
    for (const key of [...u.searchParams.keys()]) {
        if (key.startsWith("filter[")) {
            const match = key.match(/^filter\[([^\]]+)\]$/);
            if (match) {
                const apiField = match[1];
                // Only preserve if NOT managed by Tabulator
                if (!managedFields.has(apiField)) {
                    preservedFilters.set(key, u.searchParams.get(key));
                }
            }
        }
    }

    // Clear all filter params (we'll rebuild them)
    for (const key of [...u.searchParams.keys()]) {
        if (key.startsWith("filter[")) u.searchParams.delete(key);
    }

    // Restore preserved filters first
    for (const [key, value] of preservedFilters) {
        u.searchParams.set(key, value);
    }

    // Then add/override with Tabulator's filters
    for (const f of filters) {
        if (f?.field && f?.value != null && String(f.value).length) {
            const name = mapFilterFieldToApiField(f.field);
            u.searchParams.set(`filter[${name}]`, String(f.value));
        }
    }

    return u.toString();
}

/**
 * Syncs a computed API URL into:
 * - an optional "API URL" input + open links (existing behavior)
 * - window.history (optional), typically for filter query params
 *
 * @param {string} urlString - The API URL (including query params)
 * @param {string|null} targetId - DOM id of the API URL input (optional)
 * @param {"replace"|"push"|null} historySyncMode - null disables history sync
 * @param {"filters"|"all"} historySyncScope - "filters" copies only filter[...] params, "all" copies full querystring
 */
function syncBrowserUrl(urlString, targetId, historySyncMode = "replace", historySyncScope = "all") {
    const target = targetId ? document.getElementById(targetId) : null;
    const openLinks = targetId ? document.querySelectorAll(`[data-api-url-open="${targetId}"]`) : [];

    let resolvedUrl = urlString;

    try {
        resolvedUrl = decodeURIComponent(urlString);
    } catch (error) {
        resolvedUrl = urlString;
    }

    if (target) {
        target.value = resolvedUrl;
        openLinks.forEach((link) => {
            link.setAttribute("href", resolvedUrl);
        });
    }

    if (!historySyncMode) return;

    try {
        const apiUrl = new URL(urlString, window.location.origin);
        const pageUrl = new URL(window.location.href);

        if (historySyncScope === "all") {
            pageUrl.search = apiUrl.search;
        } else {
            for (const key of [...pageUrl.searchParams.keys()]) {
                if (key.startsWith("filter[")) pageUrl.searchParams.delete(key);
            }

            for (const [k, v] of apiUrl.searchParams.entries()) {
                if (k.startsWith("filter[")) pageUrl.searchParams.set(k, v);
            }
        }

        if (historySyncMode === "push") {
            window.history.pushState({}, "", pageUrl.toString());
        } else {
            window.history.replaceState({}, "", pageUrl.toString());
        }
    } catch (e) {}
}

function formatYesNo(value, trueLabel = "Yes", falseLabel = "No") {
    if (Array.isArray(value)) {
        return value.length ? trueLabel : falseLabel;
    }

    if (value && typeof value === "object") {
        return Object.keys(value).length ? trueLabel : falseLabel;
    }

    return value ? trueLabel : falseLabel;
}

function normalizeColumns(columns) {
    const formatters = {
        yesNo: (cell, params) => {
            const trueLabel = params?.trueLabel ?? "Yes";
            const falseLabel = params?.falseLabel ?? "No";

            if (!cell.getValue()) {
                return '';
            }

            return formatYesNo(cell.getValue(), trueLabel, falseLabel);
        },
        pct: (cell, params) => {
            if (typeof cell.getValue() !== "number" || isNaN(cell.getValue())) {
                return '';
            }

            const val = cell.getValue() * 100;

            if (params.suffix === false) {
                return val.toFixed(0);
            }

            return `${val.toFixed(0)}%`;
        },
        // Shows + / -N% values and hides 0%
        pctDelta: (cell, params) => {
            if (typeof cell.getValue() !== "number" || isNaN(cell.getValue())) {
                return '';
            }

            const val = cell.getValue() * 100;

            if (val === 0) {
                return '';
            }

            if (params.suffix === false) {
                return val.toFixed(0);
            }

            return `${val > 0 ? '+' : ''}${val.toFixed(0)}%`;
        },
        volumeWithUnit: (cell, params) => {
            const value = cell.getValue();

            if (value === null || value === undefined || value === '') {
                return '';
            }

            const unitField = params?.unitField ?? 'dimension.volume_converted_unit';
            const unit = unitField ? get(cell.getData(), unitField, null) : null;
            const numericValue = typeof value === "number" ? value : Number(value);
            const formatted = Number.isFinite(numericValue)
                ? new Intl.NumberFormat(params?.locale, {
                    minimumFractionDigits: params?.minimumFractionDigits ?? 0,
                    maximumFractionDigits: params?.maximumFractionDigits ?? 2,
                }).format(numericValue)
                : String(value);

            if (!unit) {
                return formatted;
            }

            return `${formatted}${params?.separator ?? ' '}${unit}`;
        },
        viewButton: (cell, params) => {
            const label = params?.label ?? "View";
            const hrefField = params?.hrefField ?? null;
            const fallbackHref = params?.href ?? "#";
            const href = hrefField ? get(cell.getData(), hrefField, fallbackHref) : fallbackHref;
            const isDisabled = params?.disabled ?? false;
            const classes = params?.class ?? "btn btn-sm btn-ghost";

            if (isDisabled) {
                return `<span class="${classes} pointer-events-none opacity-50" aria-disabled="true">${label}</span>`;
            }

            return `<a class="${classes}" href="${href}">${label}</a>`;
        },
    };

    return (columns ?? []).map((column) => {
        if (Array.isArray(column.columns) && column.columns.length > 0) {
            return { ...column, columns: normalizeColumns(column.columns) };
        }

        if (typeof column.formatter === "string" && formatters[column.formatter]) {
            return { ...column, formatter: formatters[column.formatter] };
        }

        return column;
    });
}

function buildSelectValues(options) {
    const values = { "": "All" };

    (options ?? []).forEach((option) => {
        if (option?.value === null || option?.value === "") {
            return;
        }

        values[option.value] = `${option.label} (${option.count})`;
    });

    return values;
}

function applyHeaderFilterOptionsToColumns(columns, optionsMap, payload) {
    const filters = payload?.filters ?? {};

    return (columns ?? []).map((column) => {
        if (Array.isArray(column.columns) && column.columns.length > 0) {
            return { ...column, columns: applyHeaderFilterOptionsToColumns(column.columns, optionsMap, payload) };
        }

        const filterKey = optionsMap?.[column.field];

        if (!filterKey) {
            return column;
        }

        const values = buildSelectValues(filters[filterKey] ?? []);

        return {
            ...column,
            headerFilter: column.headerFilter ?? 'list',
            headerFilterParams: {
                ...(column.headerFilterParams ?? {}),
                values,
            },
        };
    });
}

/**
 * Build a { [columnField]: sortField } map, including nested/group columns.
 */
function buildSortFieldMap(columns, map = {}) {
    (columns ?? []).forEach((column) => {
        if (column?.field && column?.sortField) {
            map[column.field] = column.sortField;
        }

        if (Array.isArray(column?.columns) && column.columns.length > 0) {
            buildSortFieldMap(column.columns, map);
        }
    });

    return map;
}

export function initTabulatorTables() {
    document.querySelectorAll("[data-tabulator]").forEach((mount) => {
        const id = mount.dataset.tabulatorId;
        const config = readJsonScript(`${id}-config`) || {};
        const initial = readJsonScript(`${id}-initial`); // can be null

        const endpoint = config.endpoint;
        const lastPagePath = config?.meta?.lastPagePath || "meta.last_page";
        const pageSize = config.pageSize ?? 25;
        const headerFilterOptionsMap = config.headerFilterOptionsMap ?? null;
        const headerFilterOptionsSeed = config.initialHeaderFilterOptions
            ?? (Array.isArray(config.initialHeaderFilter)
                ? null
                : config.initialHeaderFilter ?? null);
        const configInitialHeaderFilter = Array.isArray(config.initialFilters)
            ? config.initialFilters
            : (Array.isArray(config.initialHeaderFilter)
                ? config.initialHeaderFilter
                : false);

        const apiUrlTargetId = config.apiUrlTargetId ?? null;
        const apiUrlTarget = apiUrlTargetId ? document.getElementById(apiUrlTargetId) : null;

        // History sync options:
        // - historySyncMode: "replace" (recommended) | "push" | null (disabled)
        // - historySyncScope: "filters" (default) | "all"
        const historySyncMode = config.historySyncMode ?? "replace";
        const historySyncScope = config.historySyncScope ?? "all";
        const sortFieldMap = buildSortFieldMap(config.columns ?? []);
        const apiToColumnSortFieldMap = buildInverseSortFieldMap(sortFieldMap);

        const columns = headerFilterOptionsSeed && headerFilterOptionsMap
            ? applyHeaderFilterOptionsToColumns(config.columns ?? [], headerFilterOptionsMap, {
                filters: headerFilterOptionsSeed,
            })
            : (config.columns ?? []);

        const columnFields = collectColumnFields(columns);

        // Persistent set of API filter fields that Tabulator manages for this table instance.
        // This is what prevents "sticky" filter[...] params when a header filter is cleared.
        const managedApiFilterFields = collectManagedHeaderFilterApiFields(columns);

        // Seed table state from the current browser URL so reload keeps sort/filter/page.
        const urlState = parseJsonApiStateFromLocation({
            columnFields,
            apiToColumnSortFieldMap,
        });

        const effectiveInitialHeaderFilter = urlState.initialHeaderFilter ?? configInitialHeaderFilter;
        const effectiveInitialSort = urlState.initialSort ?? (Array.isArray(config.initialSort) ? config.initialSort : null);

        const effectivePaginationSize = urlState.paginationSize ?? pageSize;
        const effectivePaginationInitialPage = urlState.paginationInitialPage ?? null;

        // Use ajaxRequestFunc so we can:
        // - serve initial payload without an extra HTTP request
        // - build JSON:API query params ourselves
        // - normalize response into {last_page, data}
        let servedInitial = false;

        const table = new Tabulator(mount, {
            layout: "fitDataFill",

            preserveHorizontalScroll: true,

            columnDefaults: {
                ...(config.columnDefaults ?? {}),
                resizable: true,
            },

            rowHeader: {
                headerSort: false,
                resizable: false,
                minWidth: 30,
                width: 30,
                rowHandle: true,
                formatter: 'handle',
                frozen: true
            },

            columns: normalizeColumns(columns),
            movableColumns: true,
            movableRows: true,
            selectableRows: true,

            pagination: true,
            paginationMode: "remote",
            paginationSize: effectivePaginationSize,
            paginationSizeSelector: [25, 50, 100],
            ...(effectivePaginationInitialPage ? { paginationInitialPage: effectivePaginationInitialPage } : {}),

            initialHeaderFilter: effectiveInitialHeaderFilter,
            headerFilterLiveFilterDelay: 600,

            ...(effectiveInitialSort ? { initialSort: effectiveInitialSort } : {}),

            headerWordWrap: true,

            sortMode: "remote",
            sortOrderReverse: true,
            filterMode: "remote",

            ajaxURL: endpoint,
            ajaxConfig: {
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            },

            ajaxRequestFunc: (url, ajaxConfig, params) => {
                // Do not mutate Tabulator's params object.
                const requestParams = { ...params };

                // Track filters as "managed" once seen (important when cleared later).
                const requestFilters = requestParams.filter ?? requestParams.filters ?? [];
                for (const f of requestFilters) {
                    const apiField = mapFilterFieldToApiField(f?.field);
                    if (apiField) managedApiFilterFields.add(apiField);
                }

                // Map UI sort fields -> API sort fields for the request only.
                const sorters = requestParams.sorters ?? requestParams.sort ?? [];
                if (Array.isArray(sorters) && sorters.length) {
                    requestParams.sorters = sorters.map((sorter) => ({
                        ...sorter,
                        field: sortFieldMap[sorter.field] ?? sorter.field,
                    }));
                }

                const finalUrl = buildJsonApiUrl(url, requestParams, {
                    pageSize,
                    managedFilterFields: managedApiFilterFields,
                });

                syncBrowserUrl(finalUrl, apiUrlTargetId, historySyncMode, historySyncScope);

                if (!servedInitial && initial) {
                    servedInitial = true;

                    const lastPage = get(initial, lastPagePath, 1);
                    return Promise.resolve({
                        last_page: lastPage,
                        data: initial.data ?? [],
                    });
                }

                return fetch(finalUrl, { ...ajaxConfig, method: "GET" })
                    .then(async (r) => {
                        if (!r.ok) throw new Error(`HTTP ${r.status}`);
                        return r.json();
                    })
                    .then((json) => {
                        const lastPage = get(json, lastPagePath, 1);
                        return {
                            last_page: lastPage,
                            data: json.data ?? [],
                        };
                    });
            },
        });

        tabulatorTables.set(id, table);
        window.dispatchEvent(new CustomEvent("tabulator:ready", { detail: { id, table, mount } }));

        if (apiUrlTarget) {
            apiUrlTarget.addEventListener("click", () => {
                apiUrlTarget.select();
            });
        }

        window.addEventListener("resize", () => {
            table.redraw(true);
        });
    });
}
