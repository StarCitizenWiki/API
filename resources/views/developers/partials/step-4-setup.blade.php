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
