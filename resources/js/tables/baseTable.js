import {Tabulator, FilterModule, AjaxModule, SortModule, PageModule, FormatModule, EditModule} from 'tabulator-tables';

Tabulator.registerModule([FilterModule, AjaxModule, SortModule, PageModule, FormatModule, EditModule]);

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
function buildJsonApiUrl(baseUrl, params) {
    const u = new URL(baseUrl, window.location.origin);

    // pagination
    if (params.page != null) u.searchParams.set("page[number]", String(params.page));
    if (params.size != null) u.searchParams.set("page[size]", String(params.size));

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
    // Remove existing filter[...] keys first so we don't accumulate stale params
    for (const key of [...u.searchParams.keys()]) {
        if (key.startsWith("filter[")) u.searchParams.delete(key);
    }

    for (const f of filters) {
        if (f?.field && f?.value != null && String(f.value).length) {
            const name = f.field === 'created_at_human' ? 'created_at' : f.field;
            u.searchParams.set(`filter[${name}]`, String(f.value));
        }
    }

    return u.toString();
}

function syncBrowserUrl(urlString, targetId) {
    if (!targetId) {
        return;
    }

    const target = document.getElementById(targetId);
    const openLinks = document.querySelectorAll(`[data-api-url-open="${targetId}"]`);

    if (!target) {
        return;
    }

    let resolvedUrl = urlString;

    try {
        resolvedUrl = decodeURIComponent(urlString);
    } catch (error) {
        resolvedUrl = urlString;
    }

    target.value = resolvedUrl;
    openLinks.forEach((link) => {
        link.setAttribute("href", resolvedUrl);
    });
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
            return formatYesNo(cell.getValue(), trueLabel, falseLabel);
        },
        translationLabel: (cell, params) => {
            const value = cell.getValue();

            if (!value) {
                return "";
            }

            if (typeof value === "string") {
                return value;
            }

            if (typeof value === "object") {
                if (typeof value.en === "string") {
                    return value.en;
                }

                const first = Object.values(value).find((entry) => typeof entry === "string");
                return typeof first === "string" ? first : "";
            }

            return "";
        },
        translationList: (cell, params) => {
            const value = cell.getValue();

            if (!Array.isArray(value)) {
                return "";
            }

            const labels = value
                .map((entry) => {
                    if (!entry) return null;
                    if (typeof entry === "string") return entry;
                    if (typeof entry === "object") {
                        if (typeof entry.en === "string") return entry.en;
                        const first = Object.values(entry).find((item) => typeof item === "string");
                        return typeof first === "string" ? first : null;
                    }
                    return null;
                })
                .filter(Boolean);

            return labels.join(", ");
        },
        objectLabel: (cell, params) => {
            const value = cell.getValue();

            if (!value || typeof value !== "object") {
                return "";
            }

            const key = params?.key ?? "name";
            const label = value[key] ?? value.name ?? value.label ?? value.title ?? null;

            return typeof label === "string" ? label : "";
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

export function initTabulatorTables() {
    document.querySelectorAll("[data-tabulator]").forEach((mount) => {
        const id = mount.dataset.tabulatorId;
        const config = readJsonScript(`${id}-config`) || {};
        const initial = readJsonScript(`${id}-initial`); // can be null

        const endpoint = config.endpoint;
        const lastPagePath = config?.meta?.lastPagePath || "meta.last_page";
        const pageSize = config.pageSize ?? 25;
        const headerFilterOptionsMap = config.headerFilterOptionsMap ?? null;
        const headerFilterOptionsSeed = Array.isArray(config.initialHeaderFilter)
            ? null
            : config.initialHeaderFilter ?? null;
        const initialHeaderFilter = Array.isArray(config.initialHeaderFilter)
            ? config.initialHeaderFilter
            : false;
        const apiUrlTargetId = config.apiUrlTargetId ?? null;
        const apiUrlTarget = apiUrlTargetId ? document.getElementById(apiUrlTargetId) : null;
        const sortFieldMap = (config.columns ?? []).reduce((map, column) => {
            if (column?.field && column?.sortField) {
                map[column.field] = column.sortField;
            }

            return map;
        }, {});
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
            layout: "fitColumns",

            columnDefaults: config.columnDefaults ?? {},

            columns: normalizeColumns(columns),

            pagination: true,
            paginationMode: "remote",
            paginationSize: pageSize,
            initialHeaderFilter,

            sortMode: "remote",
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

                const finalUrl = buildJsonApiUrl(url, params);

                // keep URL state aligned with table state
                syncBrowserUrl(finalUrl, apiUrlTargetId);

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
