<section class="card border border-base-300 bg-base-100" aria-labelledby="developer-step-2-heading" data-testid="developer-step-2" x-data="developerShowDemo" x-init="run()">
    <div class="card-body gap-5 p-5 lg:p-6">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="flex size-6 items-center justify-center rounded-full border border-base-content/20 text-xs font-bold text-base-content">2</span>
                <h2 id="developer-step-2-heading" class="text-lg font-semibold tracking-tight">Open one record</h2>
            </div>
            <p class="text-sm text-subtle">
                Use the resource show route with the identifier from search. Add <code class="rounded bg-base-300 px-1.5 py-0.5 text-xs font-mono">include</code> to load related data such as ports and components.
            </p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="flex flex-col gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-subtle">Request</span>
                <pre class="overflow-x-auto rounded-box bg-base-300 p-4 text-xs"><code>curl "https://api.star-citizen.wiki/api/vehicles/carrack?include=ports,components"</code></pre>

                <div x-data="{ open: false }" class="flex flex-col">
                    <button type="button" class="btn btn-outline btn-sm mt-1 gap-2 self-start text-xs" x-on:click="open = !open" :aria-expanded="open.toString()">
                        <x-icon name="code" class="size-3.5" />
                        <span x-show="!open">View code examples</span>
                        <span x-show="open" style="display: none;">Hide code examples</span>
                        <span class="badge badge-ghost badge-xs">JS · Python · PHP</span>
                    </button>
                    <div x-show="open" x-transition style="display: none;" class="flex flex-col gap-3 mt-2">
                        <div>
                            <span class="text-xs font-semibold text-subtle">JavaScript</span>
                            <pre class="mt-1 overflow-x-auto rounded-box bg-base-300 p-3 text-xs"><code>const response = await fetch(
  "https://api.star-citizen.wiki/api/vehicles/carrack?include=ports,components"
);
const { data } = await response.json();
console.log(data.name, data.ports);</code></pre>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-subtle">Python</span>
                            <pre class="mt-1 overflow-x-auto rounded-box bg-base-300 p-3 text-xs"><code>import requests

vehicle = requests.get(
    "https://api.star-citizen.wiki/api/vehicles/carrack",
    params={"include": "ports,components"},
    timeout=10,
).json()
print(vehicle["data"]["name"])</code></pre>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-subtle">PHP</span>
                            <pre class="mt-1 overflow-x-auto rounded-box bg-base-300 p-3 text-xs"><code>$response = file_get_contents(
    'https://api.star-citizen.wiki/api/vehicles/carrack?include=ports,components'
);
$vehicle = json_decode($response, true);
echo $vehicle['data']['name'];</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-subtle">Response</span>
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
            For lists, use index routes with filters discovered from the API.
        </p>
    </div>
</section>
