import {Tabulator, FilterModule, AjaxModule, SortModule, PageModule} from 'tabulator-tables';

Tabulator.registerModule([FilterModule, AjaxModule, SortModule, PageModule]);


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
    const sorters = params.sorters ?? [];
    if (sorters.length) {
        const sort = sorters.map(s => (s.dir === "desc" ? "-" : "") + s.field).join(",");
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
            u.searchParams.set(`filter[${f.field}]`, String(f.value));
        }
    }

    return u.toString();
}

// Optional: keep browser URL in sync with table state (JSON:API params)
function syncBrowserUrl(urlString) {
    const u = new URL(urlString, window.location.origin);
    const newUrl = `${u.pathname}?${u.searchParams.toString()}`;
    window.history.replaceState({}, "", newUrl);
}

export function initTabulatorTables() {
    document.querySelectorAll("[data-tabulator]").forEach((mount) => {
        const id = mount.dataset.tabulatorId;
        const config = readJsonScript(`${id}-config`) || {};
        const initial = readJsonScript(`${id}-initial`); // can be null

        const endpoint = config.endpoint;
        const lastPagePath = config?.meta?.lastPagePath || "meta.last_page";
        const pageSize = config.pageSize ?? 25;

        // Use ajaxRequestFunc so we can:
        // - serve initial payload without an extra HTTP request
        // - build JSON:API query params ourselves
        // - normalize response into {last_page, data}
        let servedInitial = false;

        const table = new Tabulator(mount, {
            layout: "fitColumns",

            columns: config.columns ?? [],

            pagination: true,
            paginationMode: "remote",
            paginationSize: pageSize,

            sortMode: "remote",
            filterMode: "remote",

            ajaxURL: endpoint,
            ajaxConfig: {
                credentials: "same-origin", // send cookies for Sanctum web auth
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            },

            ajaxRequestFunc: (url, ajaxConfig, params) => {
                const finalUrl = buildJsonApiUrl(url, params);

                // keep URL state aligned with table state
                syncBrowserUrl(finalUrl);

                // serve the initial payload once (first load) to avoid duplicate request
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

        // If your page initially rendered with ?page[number]=2 etc,
        // the controller already fetched that initial payload accordingly.
        // Tabulator will display it immediately via initial payload above.
    });
}
