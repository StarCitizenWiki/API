@extends('layouts.app')

@section('title', 'Developer Quickstart - Star Citizen Wiki API')
@section('meta_description', 'Get started with the Star Citizen Wiki API. Search, fetch, and filter game data in minutes.')

@push('meta')
    <meta property="og:title" content="Developer Quickstart - Star Citizen Wiki API">
    <meta property="og:description" content="Get started with the Star Citizen Wiki API. Search, fetch, and filter game data in minutes.">
@endpush

@section('content')
    <div class="mx-auto flex max-w-6xl flex-col gap-6 py-2" data-testid="developer-page">
        <section class="flex flex-col gap-2">
            <span class="badge badge-ghost mb-3 text-xs tracking-widest uppercase">Developer Quickstart</span>
            <h1 class="text-3xl font-bold sm:text-4xl mb-3" data-testid="developer-page-title">
                Star Citizen Wiki API
            </h1>
            <p class="text-subtle mb-5 max-w-md">
                Browse ships, items, locations, and more via JSON. Powering starcitizen.tools.
            </p>
        </section>


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
                    For lists, use index routes with filters from the OpenAPI documentation or /filters endpoint.
                </p>
            </div>
        </section>

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

        <section class="card border border-base-300 bg-base-100" aria-labelledby="developer-step-4-heading" data-testid="developer-step-4">
            <div class="card-body gap-5 p-5 lg:p-6">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="flex size-6 items-center justify-center rounded-full border border-base-content/20 text-xs font-bold text-base-content">4</span>
                        <h2 id="developer-step-4-heading" class="text-lg font-semibold tracking-tight">Set up your project</h2>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="flex flex-col gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-base-content">Generate client types</h3>
                            <p class="mt-1 text-xs text-subtle">Use the OpenAPI specification with client generators or type generation tools.</p>
                            <pre class="mt-2 overflow-x-auto rounded-box bg-base-300 p-4 text-xs"><code>npx openapi-typescript https://api.star-citizen.wiki/api/openapi -o sc-api.d.ts</code></pre>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-base-content">Links</h3>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <a href="https://docs.star-citizen.wiki" class="btn btn-primary btn-sm" data-testid="developer-api-reference-link">
                                    <x-icon name="book-open" class="size-4" />
                                    API Reference
                                </a>
                                <a href="{{ url('/api/openapi') }}" class="btn btn-outline btn-sm" data-testid="developer-openapi-link">
                                    <x-icon name="code-xml" class="size-4" />
                                    OpenAPI
                                </a>
                                <a href="https://github.com/StarCitizenWiki/API" class="btn btn-ghost btn-sm" data-testid="developer-github-link">
                                    Source Code
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-base-content">Usage</h3>
                            <div class="mt-2 space-y-2 text-sm">
                                <p class="text-base-content">
                                    Please credit <a class="link link-primary" href="https://api.star-citizen.wiki">api.star-citizen.wiki</a> in public projects.
                                </p>
                                <p class="text-base-content">
                                    Commercial use is not permitted under the <a class="link link-primary" href="https://support.robertsspaceindustries.com/hc/en-us/articles/360006895793-Star-Citizen-Fankit-and-Fandom-FAQ">RSI Fandom FAQ</a>.
                                </p>
                                <p class="text-xs text-subtle">
                                    Search is rate-limited to 60 requests/min/IP. API health: <a class="link link-primary" href="{{ url('/up') }}">{{ url('/up') }}</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="mx-auto max-w-6xl pb-10">
        <div class="flex flex-col items-center gap-4 rounded-box border border-base-300 p-4 sm:flex-row sm:p-5">
            <img src="{{ asset('MadeByTheCommunity_White.png') }}" alt="Made by the Community" class="h-10 shrink-0" />
            <p class="text-xs text-center text-subtle sm:text-start">
                This is an unofficial Star Citizen fan site, not affiliated with the
                <a href="https://robertsspaceindustries.com" target="_blank" rel="noopener noreferrer" class="link-primary">Cloud Imperium</a>
                group of companies. All content on this site not authored by its host or users are property of their respective owners.
                Visit the <a href="https://robertsspaceindustries.com" target="_blank" rel="noopener noreferrer" class="link-primary">official Star Citizen website</a>.
            </p>
        </div>
    </div>
@endsection
