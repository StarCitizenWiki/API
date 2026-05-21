export function developerQuickstart() {
    return {
        query: "carrack",
        loading: false,
        error: "",
        responseText: "",
        resultCount: null,

        get trimmedQuery() {
            return this.query.trim();
        },

        get publicUrl() {
            return `https://api.star-citizen.wiki/api/search?filter[query]=${encodeURIComponent(this.trimmedQuery || "carrack")}`;
        },

        get curlCommand() {
            return `curl -G "https://api.star-citizen.wiki/api/search" \\\n  --data-urlencode "filter[query]=${this.trimmedQuery || "carrack"}"`;
        },

        requestPath() {
            const params = new URLSearchParams();
            params.set("filter[query]", this.trimmedQuery);

            return `/api/search?${params.toString()}`;
        },

        async run() {
            this.error = "";
            this.responseText = "";
            this.resultCount = null;

            if (this.trimmedQuery.length < 2) {
                this.error = "Enter at least two characters.";

                return;
            }

            this.loading = true;

            try {
                const response = await fetch(this.requestPath(), {
                    headers: { Accept: "application/json" },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();
                this.resultCount = Array.isArray(payload.data) ? payload.data.length : null;
                this.responseText = this.formatPreview(payload);
            } catch {
                this.error = "Request failed. Check the API status.";
            } finally {
                this.loading = false;
            }
        },

        formatPreview(payload) {
            const preview = JSON.parse(JSON.stringify(payload));

            if (Array.isArray(preview.data) && preview.data.length > 5) {
                preview.data = preview.data.slice(0, 5);
                preview.meta = {
                    ...(preview.meta || {}),
                    preview: "Showing the first 5 results.",
                };
            }

            const text = JSON.stringify(preview, null, 2);

            return text.length <= 8192 ? text : `${text.slice(0, 8192)}\n... response truncated`;
        },
    };
}

export function developerShowDemo() {
    return {
        loading: false,
        error: "",
        responseText: "",

        async run() {
            this.error = "";
            this.responseText = "";
            this.loading = true;

            try {
                const response = await fetch("/api/vehicles/carrack?include=ports,components", {
                    headers: { Accept: "application/json" },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();
                this.responseText = this.formatPreview(payload);
            } catch {
                this.error = "Request failed. Check the API status.";
            } finally {
                this.loading = false;
            }
        },

        formatPreview(payload) {
            const text = JSON.stringify(payload, null, 2);

            return text.length <= 8192 ? text : `${text.slice(0, 8192)}\n... response truncated`;
        },
    };
}

export function developerFiltersDemo() {
    return {
        loading: false,
        error: "",
        responseText: "",

        async run() {
            this.error = "";
            this.responseText = "";
            this.loading = true;

            try {
                const response = await fetch("/api/vehicles/filters", {
                    headers: { Accept: "application/json" },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();
                this.responseText = JSON.stringify(payload, null, 2);
            } catch {
                this.error = "Request failed. Check the API status.";
            } finally {
                this.loading = false;
            }
        },
    };
}
