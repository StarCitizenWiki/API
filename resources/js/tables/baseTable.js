import {
    Tabulator,
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

Tabulator.registerModule([
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

    // Build a Set of fields that Tabulator is managing
    const managedFields = new Set(filters.map(f => f?.field).filter(Boolean));

    // Preserve existing filter[...] keys that aren't managed by Tabulator
    const preservedFilters = new Map();
    for (const key of [...u.searchParams.keys()]) {
        if (key.startsWith("filter[")) {
            const match = key.match(/^filter\[([^\]]+)\]$/);
            if (match) {
                const field = match[1];
                // Only preserve if NOT managed by Tabulator
                if (!managedFields.has(field)) {
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
            const name = f.field === 'created_at_human' ? 'created_at' : f.field;
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
        const initialHeaderFilter = Array.isArray(config.initialFilters)
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

        const columns = headerFilterOptionsSeed && headerFilterOptionsMap
            ? applyHeaderFilterOptionsToColumns(config.columns ?? [], headerFilterOptionsMap, {
                filters: headerFilterOptionsSeed,
            })
            : (config.columns ?? []);

        // Use ajaxRequestFunc so we can:
        // - serve initial payload without an extra HTTP request
        // - build JSON:API query params ourselves
        // - normalize response into {last_page, data}
        let servedInitial = false;

        const table = new Tabulator(mount, {
            layout: "fitData",

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
            selectableRows:true,

            pagination: true,
            paginationMode: "remote",
            paginationSize: pageSize,
            paginationSizeSelector: [25, 50, 100],
            initialHeaderFilter,
            headerFilterLiveFilterDelay: 600,

            headerWordWrap: true,

            // sortMode: "remote",
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
                const sorters = params.sorters ?? params.sort ?? [];

                if (Array.isArray(sorters) && sorters.length) {
                    params.sorters = sorters.map((sorter) => ({
                        ...sorter,
                        field: sortFieldMap[sorter.field] ?? sorter.field,
                    }));
                }

                const finalUrl = buildJsonApiUrl(url, params, { pageSize });

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
