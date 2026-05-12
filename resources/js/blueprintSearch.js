/**
 * Alpine component for the blueprint search panel.
 *
 * Replaces the inline initSearchPanel() script (~444 lines).
 * Reads config from the #blueprint-show-data JSON payload.
 */
export function blueprintSearch() {
    return {
        query: "",
        results: [],
        resultCount: 0,
        loading: false,
        showEmpty: false,
        emptyMessage: "",

        // Resource filter state
        filterOpen: false,
        resourceTypes: [],
        selectedUuids: [],
        filterLoading: false,

        // Config (loaded from JSON payload in init)
        _config: {},
        _initialResults: [],
        _initialResultCount: 0,
        _activeController: null,
        _debounceTimer: null,

        // --- Computed-like getters ---

        get selectedCount() {
            return this.selectedUuids.length;
        },

        // --- Init ---

        init() {
            const el = document.getElementById("blueprint-show-data");
            if (!el?.textContent) return;

            try {
                const payload = JSON.parse(el.textContent);
                const s = payload.search ?? {};

                this._config = {
                    apiEndpoint: strVal(s.apiEndpoint),
                    resourceTypesEndpoint: strVal(s.resourceTypesEndpoint),
                    currentBlueprintUuid: strVal(s.currentBlueprintUuid) || null,
                    version: strVal(s.version),
                };
                this._initialResults = Array.isArray(s.initialResults) ? s.initialResults : [];
                this._initialResultCount = numVal(s.initialResultCount, this._initialResults.length);
                this.selectedUuids = Array.isArray(s.selectedResourceTypeUuids) ? [...s.selectedResourceTypeUuids] : [];
            } catch (e) {
                return;
            }

            // Read initial query from the input element (set by PHP value="" attribute)
            this.$nextTick(() => {
                this.query = this.$refs.searchInput?.value?.trim() ?? "";
            });

            // Show initial results
            this.results = [...this._initialResults];
            this.resultCount = this._initialResultCount;
            this.syncEmptyState();

            this.loadResourceFilters();
        },

        // --- Search ---

        onSearchInput() {
            clearTimeout(this._debounceTimer);
            this._debounceTimer = setTimeout(() => this.performSearch(), 180);
        },

        async performSearch() {
            const q = this.query.trim();
            const selected = [...this.selectedUuids];

            if (q === "" && selected.length === 0) {
                this._activeController?.abort();
                this._activeController = null;
                this.loading = false;
                this.results = [...this._initialResults];
                this.resultCount = this._initialResultCount;
                this.syncEmptyState();
                return;
            }

            if (!this._config.apiEndpoint) {
                this.results = [];
                this.resultCount = 0;
                this.emptyMessage = "Unable to load blueprint results right now.";
                this.showEmpty = true;
                return;
            }

            this._activeController?.abort();
            const controller = new AbortController();
            this._activeController = controller;
            this.loading = true;

            try {
                const url = new URL(this._config.apiEndpoint, window.location.origin);
                if (this._config.version && !url.searchParams.has("version")) {
                    url.searchParams.set("version", this._config.version);
                }
                url.searchParams.delete("filter[query]");
                url.searchParams.delete("filter[ingredient.uuid]");
                if (q !== "") url.searchParams.set("filter[query]", q);
                if (selected.length > 0) url.searchParams.set("filter[ingredient.uuid]", selected.join(","));

                const response = await fetch(url.toString(), {
                    headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
                    signal: controller.signal,
                });

                if (!response.ok) throw new Error("Unable to load blueprints.");
                const body = await response.json();

                if (this._activeController !== controller || controller.signal.aborted) return;

                this.results = Array.isArray(body?.data) ? body.data : [];
                this.resultCount = numVal(body?.meta?.total, this.results.length);
                this.loading = false;
                this.syncEmptyState();
            } catch (error) {
                if (error instanceof DOMException && error.name === "AbortError") return;
                if (this._activeController === controller) {
                    this.results = [];
                    this.resultCount = 0;
                    this.emptyMessage = "Unable to load blueprint results right now.";
                    this.showEmpty = true;
                    this.loading = false;
                    this._activeController = null;
                }
            }
        },

        syncEmptyState() {
            if (this.results.length > 0) {
                this.showEmpty = false;
                return;
            }
            this.showEmpty = true;
            this.emptyMessage =
                this.query.trim() === "" && this.selectedUuids.length === 0
                    ? "Search by output name or pick resource filters to load matching blueprints."
                    : "No blueprints matched the current search and resource filters.";
        },

        // --- Result rendering helpers ---

        pluralizeResults(count) {
            return `${count} result${count === 1 ? "" : "s"}`;
        },

        buildResultUrl(result) {
            const endpoint = strVal(result?.web_url) || strVal(result?.link);
            if (!endpoint) return "";

            const url = new URL(endpoint, window.location.origin);
            if (this._config.version && !url.searchParams.has("version")) {
                url.searchParams.set("version", this._config.version);
            }
            url.searchParams.delete("filter[query]");
            url.searchParams.delete("filter[ingredient.uuid]");
            if (this.selectedUuids.length > 0) {
                url.searchParams.set("filter[ingredient.uuid]", this.selectedUuids.join(","));
            }
            return url.toString();
        },

        resultCardClass(result) {
            const sel = this.isCurrentBlueprint(result);
            return sel
                ? "group rounded-box border border-primary/40 bg-primary/5 px-4 py-4 shadow-sm transition hover:border-primary/50 hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
                : "group rounded-box border border-base-300 bg-base-100 px-4 py-4 shadow-sm transition hover:border-primary/40 hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30";
        },

        isCurrentBlueprint(result) {
            return this._config.currentBlueprintUuid != null && strVal(result?.uuid) === this._config.currentBlueprintUuid;
        },

        resultTypeLabel(result) {
            return [strVal(result?.output?.type), strVal(result?.output?.sub_type)].filter(Boolean).join(" / ");
        },

        ingredientPreview(ingredients) {
            if (!Array.isArray(ingredients)) return "";
            const names = ingredients
                .map((i) => (typeof i?.name === "string" ? i.name.trim() : ""))
                .filter((n, idx, arr) => n !== "" && arr.indexOf(n) === idx);
            const visible = names.slice(0, 3);
            if (visible.length === 0) return "";
            const overflow = names.length - visible.length;
            return overflow > 0 ? `${visible.join(", ")} +${overflow} more` : visible.join(", ");
        },

        formatCraftTime(seconds) {
            const s = numVal(seconds, -1);
            if (s < 0) return null;
            if (s <= 60) return `${s} second${s === 1 ? "" : "s"}`;
            if (s < 3600) {
                const m = Math.floor(s / 60);
                const r = s % 60;
                if (r === 0) return `${m} minute${m === 1 ? "" : "s"}`;
                return `${m} minute${m === 1 ? "" : "s"} ${r} second${r === 1 ? "" : "s"}`;
            }
            const h = Math.floor(s / 3600);
            const rm = Math.floor((s % 3600) / 60);
            if (rm === 0) return `${h} hour${h === 1 ? "" : "s"}`;
            return `${h} hour${h === 1 ? "" : "s"} ${rm} minute${rm === 1 ? "" : "s"}`;
        },

        onResultClick(result, event) {
            if (this._config.currentBlueprintUuid && strVal(result?.uuid) === this._config.currentBlueprintUuid) {
                event.preventDefault();
                document.getElementById("blueprint-recipe-flow")?.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        },

        // --- Resource filter ---

        toggleFilter() {
            this.filterOpen = !this.filterOpen;
        },

        isResourceSelected(uuid) {
            return this.selectedUuids.includes(uuid);
        },

        toggleResourceSelection(uuid) {
            const idx = this.selectedUuids.indexOf(uuid);
            if (idx >= 0) {
                this.selectedUuids.splice(idx, 1);
            } else {
                this.selectedUuids.push(uuid);
            }
            this.onSearchInput();
        },

        clearAllFilters() {
            this.selectedUuids = [];
            this.onSearchInput();
        },

        removeFilterChip(uuid) {
            const idx = this.selectedUuids.indexOf(uuid);
            if (idx >= 0) this.selectedUuids.splice(idx, 1);
            this.onSearchInput();
        },

        filterToggleClass() {
            return this.selectedCount === 0
                ? "btn btn-outline min-h-12 justify-between border-base-300 bg-base-100 text-base-content"
                : "btn btn-outline btn-primary min-h-12 justify-between border-primary/40 bg-primary/5 text-primary";
        },

        filterOptionClass(uuid) {
            return this.isResourceSelected(uuid)
                ? "flex cursor-pointer items-start gap-3 rounded-box border border-primary/40 bg-primary/5 px-3 py-3 transition-colors"
                : "flex cursor-pointer items-start gap-3 rounded-box border border-base-300 bg-base-100 px-3 py-3 transition-colors hover:border-primary/30 hover:bg-primary/5";
        },

        async loadResourceFilters() {
            if (!this._config.resourceTypesEndpoint) {
                this.resourceTypes = [];
                return;
            }
            try {
                const url = new URL(this._config.resourceTypesEndpoint, window.location.origin);
                if (this._config.version && !url.searchParams.has("version")) {
                    url.searchParams.set("version", this._config.version);
                }
                const response = await fetch(url.toString(), {
                    headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
                });
                if (!response.ok) throw new Error("Unable to load resource filters.");
                const body = await response.json();
                this.resourceTypes = Array.isArray(body?.data) ? body.data : [];
            } catch (e) {
                this.resourceTypes = [];
            }
        },
    };
}

// --- Shared utility functions ---

function numVal(value, fallback = 0) {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
}

function strVal(value, fallback = "") {
    return typeof value === "string" && value !== "" ? value : fallback;
}
