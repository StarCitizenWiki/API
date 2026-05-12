import { createIcons, icons } from "lucide";

const DEBOUNCE_MS = 250;
const MIN_QUERY_LENGTH = 2;
const MAX_RESULTS = 15;

export function liveSearch(apiEndpoint) {
    return {
        query: "",
        results: [],
        open: false,
        activeIndex: -1,
        apiEndpoint: apiEndpoint,
        _abortController: null,
        _debounceTimer: null,
        _repositionHandler: null,

        init() {
            this._repositionHandler = () => {
                if (this.open) this.updateDropdownPosition();
            };
            window.addEventListener("scroll", this._repositionHandler, true);
            window.addEventListener("resize", this._repositionHandler);
        },

        destroy() {
            if (this._repositionHandler) {
                window.removeEventListener("scroll", this._repositionHandler, true);
                window.removeEventListener("resize", this._repositionHandler);
            }
        },

        updateDropdownPosition() {
            const input = this.$refs.input;
            const dropdown = this.$refs.dropdown;
            if (!input || !dropdown) return;

            const rect = input.getBoundingClientRect();
            const parentRect = input.parentElement.getBoundingClientRect();
            dropdown.style.position = "fixed";
            dropdown.style.zIndex = "9999";
            dropdown.style.top = `${rect.bottom + 4}px`;
            dropdown.style.left = `${parentRect.left}px`;
            dropdown.style.width = `${parentRect.width}px`;
        },

        search() {
            clearTimeout(this._debounceTimer);
            const q = this.query.trim();

            if (q.length < MIN_QUERY_LENGTH) {
                this.results = [];
                this.open = false;
                return;
            }

            this._debounceTimer = setTimeout(async () => {
                if (this._abortController) {
                    this._abortController.abort();
                }
                this._abortController = new AbortController();

                const data = await this.fetchResults(q);
                if (!data) return;

                this.results = data;
                this.activeIndex = -1;
                this.open = true;
                this.$nextTick(() => {
                    this.updateDropdownPosition();
                    createIcons({ icons });
                });
            }, DEBOUNCE_MS);
        },

        navigate(e) {
            if (!this.open || this.results.length === 0) return;

            if (e.key === "ArrowDown") {
                e.preventDefault();
                this.activeIndex = Math.min(this.activeIndex + 1, this.results.length - 1);
                this.scrollActiveIntoView();
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                this.activeIndex = Math.max(this.activeIndex - 1, -1);
                if (this.activeIndex === -1) {
                    this.$refs.input.focus();
                }
            } else if (e.key === "Enter") {
                if (this.activeIndex >= 0 && this.results[this.activeIndex]) {
                    e.preventDefault();
                    const url = this.results[this.activeIndex].web_url;
                    if (url) window.location.href = url;
                }
            } else if (e.key === "Escape") {
                this.open = false;
                this.$refs.input.blur();
            }
        },

        scrollActiveIntoView() {
            this.$nextTick(() => {
                const items = this.$refs.dropdown?.querySelectorAll("[data-live-search-item]");
                if (items && items[this.activeIndex]) {
                    items[this.activeIndex].scrollIntoView({ block: "nearest" });
                }
            });
        },

        selectItem(index) {
            const url = this.results[index]?.web_url;
            if (url) window.location.href = url;
        },

        closeOnOutside(e) {
            const dropdown = this.$refs.dropdown;
            const input = this.$refs.input;
            if (dropdown && input && !dropdown.contains(e.target) && e.target !== input) {
                this.open = false;
            }
        },

        itemLabel(item) {
            const parts = [
                item.classification,
                item.item_type_label,
                item.extra_label,
            ].filter(Boolean);
            if (parts.length === 0 && item.class_name) {
                parts.push(item.class_name);
            }
            return parts.length > 0 ? `(${parts.join(" · ")})` : "";
        },

        async fetchResults(query) {
            const url = `${this.apiEndpoint}?filter[query]=${encodeURIComponent(query)}&limit=${MAX_RESULTS}`;

            try {
                const response = await fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                    signal: this._abortController.signal,
                });

                const json = await response.json();
                const isGrouped =
                    Array.isArray(json.data) &&
                    json.data.some((item) => Array.isArray(item.results));

                if (isGrouped) {
                    return json.data.flatMap((group) =>
                        group.results.map((result) => ({
                            name: result.name ?? result.title,
                            classification: result.classification_label,
                            class_name: result.class_name,
                            web_url: result.web_url,
                            type_label: group.label,
                            item_type_label: result.item_type_label ?? null,
                            extra_label: result.extra_label ?? null,
                        })),
                    );
                }

                return (json.data ?? []).map((item) => ({
                    name: item.name ?? item.title,
                    classification: item.classification_label,
                    class_name: item.class_name,
                    web_url: item.web_url,
                }));
            } catch (e) {
                if (e.name === "AbortError") {
                    return null;
                }
                throw e;
            }
        },
    };
}
