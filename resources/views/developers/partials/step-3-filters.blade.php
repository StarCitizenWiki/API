<section class="card border border-base-300 bg-base-100" aria-labelledby="developer-step-3-heading" data-testid="developer-step-3" x-data="developerFiltersDemo" x-init="run()">
    <div class="card-body gap-5 p-5 lg:p-6">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="flex size-6 items-center justify-center rounded-full border border-base-content/20 text-xs font-bold text-base-content">3</span>
                <h2 id="developer-step-3-heading" class="text-lg font-semibold tracking-tight">Browse with filters</h2>
            </div>
            <p class="text-sm text-subtle">
                Index routes return paginated lists. Use the filters endpoint to discover supported filter fields and values before building a query. The same pattern is available across vehicles, items, locations, missions, and commodities.
            </p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-subtle">Available facets</span>
                    <pre class="overflow-x-auto rounded-box bg-base-300 p-4 text-xs"><code>curl "https://api.star-citizen.wiki/api/vehicles/filters"</code></pre>
                </div>

                <div class="flex flex-col gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-subtle">Filtered index</span>
                    <pre class="overflow-x-auto rounded-box bg-base-300 p-4 text-xs"><code>curl -G "https://api.star-citizen.wiki/api/vehicles" \
  --data-urlencode "filter[manufacturer]=Anvil Aerospace"</code></pre>
                </div>

                <div class="rounded-box bg-base-200 p-3 text-xs text-subtle space-y-1">
                    <p class="font-semibold text-base-content">Same shape for every game resource:</p>
                    <p><code class="rounded bg-base-300 px-1 py-0.5 font-mono">/api/vehicles</code> <code class="rounded bg-base-300 px-1 py-0.5 font-mono">/api/items</code> <code class="rounded bg-base-300 px-1 py-0.5 font-mono">/api/locations</code></p>
                    <p><code class="rounded bg-base-300 px-1 py-0.5 font-mono">/api/missions</code> <code class="rounded bg-base-300 px-1 py-0.5 font-mono">/api/commodities</code></p>
                    <p class="pt-1">Comm-links and Galactapedia follow the same pattern with field filters like <code class="rounded bg-base-300 px-1 py-0.5 font-mono">filter[title]</code> and <code class="rounded bg-base-300 px-1 py-0.5 font-mono">filter[content]</code>.</p>
                    <p>Pin game data to a patch: <code class="rounded bg-base-300 px-1 py-0.5 font-mono">?version=</code> using <code class="rounded bg-base-300 px-1 py-0.5 font-mono">/api/game-versions/default</code>.</p>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-subtle">Facet response</span>
                <div class="min-h-28 rounded-box bg-base-300 p-4" aria-live="polite">
                    <div class="flex items-center gap-2 text-sm text-subtle" x-show="loading" style="display: none;">
                        <span class="loading loading-spinner loading-sm"></span>
                        Loading&hellip;
                    </div>
                    <p class="text-sm text-error" x-show="error" x-text="error" style="display: none;"></p>
                    <pre class="max-h-96 overflow-auto text-xs" x-show="! loading && ! error && responseText" style="display: none;"><code x-text="responseText"></code></pre>
                </div>
            </div>
        </div>

        <p class="text-sm text-subtle">
            <x-icon name="arrow-right" class="inline size-3.5 text-primary" />
            Once your requests are working, generate types or clients from the OpenAPI specification.
        </p>
    </div>
</section>
