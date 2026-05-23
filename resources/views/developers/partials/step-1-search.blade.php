<section class="card border border-primary/20 bg-base-100" aria-labelledby="developer-step-1-heading" data-testid="developer-step-1" x-data="developerQuickstart" x-init="run()">
    <div class="card-body gap-5 p-5 lg:p-6">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex size-6 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-content">1</span>
                    <h2 id="developer-step-1-heading" class="text-lg font-semibold tracking-tight">Find a resource</h2>
                </div>
                <p class="text-sm text-subtle">
                    Search across ships, items, locations, and other resources when you only have a name. The response includes the resource type and identifier to use next.
                </p>
            </div>
            <a class="link link-primary text-sm shrink-0" :href="publicUrl" target="_blank" rel="noreferrer">Open full request</a>
        </div>

        <form class="join w-full max-w-xl" x-on:submit.prevent="run()">
            <label class="sr-only" for="developer-search-query">Search query</label>
            <input
                id="developer-search-query"
                class="input join-item input-bordered w-full"
                type="search"
                x-model="query"
                placeholder="carrack"
                autocomplete="off"
            >
            <button class="btn btn-primary join-item" type="submit" :disabled="loading">
                <span class="loading loading-spinner loading-xs" x-show="loading" style="display: none;"></span>
                <span x-show="! loading">Run</span>
            </button>
        </form>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="flex flex-col gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-subtle">Request</span>
                <pre class="min-h-28 overflow-x-auto rounded-box bg-base-300 p-4 text-xs"><code x-text="curlCommand"></code></pre>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-wide text-subtle">Response</span>
                </div>
                <div class="min-h-28 rounded-box bg-base-300 p-4" aria-live="polite">
                    <div class="flex items-center gap-2 text-sm text-subtle" x-show="loading" style="display: none;">
                        <span class="loading loading-spinner loading-sm"></span>
                        Loading&hellip;
                    </div>
                    <p class="text-sm text-error" x-show="error" x-text="error" style="display: none;"></p>
                    <pre class="max-h-96 overflow-auto text-xs" x-show="! loading && ! error && responseText" style="display: none;"><code x-text="responseText"></code></pre>
                    <p class="text-sm text-subtle" x-show="! loading && ! error && ! responseText">No results yet.</p>
                </div>
            </div>
        </div>

        <p class="text-sm text-subtle">
            <x-icon name="arrow-right" class="inline size-3.5 text-primary" />
            Search returns a resource type and identifier. Use them to request the full Carrack vehicle record.
        </p>
    </div>
</section>
