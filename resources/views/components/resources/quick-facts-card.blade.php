@props(['resource'])

@php
    $kind = data_get($resource, 'kind');
    $signature = data_get($resource, 'signature');
    $density = data_get($resource, 'density_g_per_cc');
    $instability = data_get($resource, 'instability');
    $resistance = data_get($resource, 'resistance');
    $refinedVersion = data_get($resource, 'refined_version');
    $refinedName = data_get($refinedVersion, 'name');
    $refinedUuid = data_get($refinedVersion, 'uuid');
    $refinedUrl = data_get($refinedVersion, 'web_url');
    $rawVersions = data_get($resource, 'raw_versions', []);
    $boxSizes = data_get($resource, 'box_sizes_scu', []);
    $systems = data_get($resource, 'systems', []);
    $isMineable = data_get($resource, 'is_mineable');
    $hasShip = data_get($resource, 'has_ship_mineables');
    $hasGround = data_get($resource, 'has_ground_vehicle_mineables');
    $hasFps = data_get($resource, 'has_fps_mineables');
    $hasHarvest = data_get($resource, 'has_harvestables');
    $hasSalvage = data_get($resource, 'has_salvage');

    $versionQuery = request()->query('version');
    $indexRoute = route('web.commodities.index');
    if (is_string($versionQuery) && $versionQuery !== '') {
        $indexRoute = url()->query($indexRoute, ['version' => $versionQuery]);
    }

    $methods = data_get($resource, 'methods', []);
@endphp

<section {{ $attributes->merge(['class' => 'card h-full border border-base-300 bg-base-100 shadow xl:col-span-5', 'data-testid' => 'resource-quick-facts']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-5">
                <section class="min-w-0 space-y-3">
                    <div class="text-sm font-semibold text-base-content/65">Properties</div>

                    <dl class="space-y-2">
                        <div class="grid grid-cols-2 items-start gap-x-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Kind</dt>
                            <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                {{ $kind ? ucfirst($kind) : '-' }}
                            </dd>
                        </div>
                        <div class="grid grid-cols-2 items-start gap-x-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Refines To</dt>
                            <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                @if ($refinedUrl)
                                    <span class="text-base-content/35 mr-1">→</span>
                                    <a class="link link-hover link-primary font-semibold" href="{{ $refinedUrl }}">{{ $refinedName }}</a>
                                @else
                                    {{ $refinedName ?? '-' }}
                                @endif
                            </dd>
                        </div>
                        @if (is_array($rawVersions) && $rawVersions !== [])
                            <div class="grid grid-cols-2 items-start gap-x-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Raw Versions</dt>
                                <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                    @foreach ($rawVersions as $i => $rawVersion)
                                        @if ($i > 0), @endif
                                        <span class="text-base-content/35 mr-1">←</span>
                                        @if (($rawVersion['web_url'] ?? null) !== null)
                                            <a class="link link-hover link-primary font-semibold" href="{{ $rawVersion['web_url'] }}">{{ $rawVersion['name'] }}</a>
                                        @else
                                            {{ $rawVersion['name'] }}
                                        @endif
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                        <div class="grid grid-cols-2 items-start gap-x-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Signature</dt>
                            <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                {{ $signature ?? '-' }}
                            </dd>
                        </div>
                        <div class="grid grid-cols-2 items-start gap-x-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Density</dt>
                            <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                {{ $density !== null ? $density.' g/cc' : '-' }}
                            </dd>
                        </div>
                        <div class="grid grid-cols-2 items-start gap-x-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Instability</dt>
                            <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                {{ $instability ?? '-' }}
                            </dd>
                        </div>
                        <div class="grid grid-cols-2 items-start gap-x-3">
                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Resistance</dt>
                            <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                {{ $resistance ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>

            <section class="space-y-5 pt-4 md:border-t-0 md:pt-0 md:pl-5">
                <div class="space-y-3">
                    <div class="text-sm font-semibold text-base-content/65">Mining Methods</div>

                    @if ($methods !== [])
                        <div class="flex flex-wrap gap-2">
                            @foreach ($methods as $method)
                                <span class="badge badge-outline badge-sm">{{ $method }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">-</div>
                    @endif
                </div>

                <div class="space-y-3">
                    <div class="text-sm font-semibold text-base-content/65">Systems</div>

                    @if ($systems !== [])
                        <div class="flex flex-wrap gap-2">
                            @foreach ($systems as $system)
                                <span class="badge badge-ghost badge-sm">{{ $system }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">-</div>
                    @endif
                </div>

                @if (is_array($boxSizes) && $boxSizes !== [])
                    <div class="space-y-3">
                        <div class="text-sm font-semibold text-base-content/65">Box Sizes (SCU)</div>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($boxSizes as $size)
                                <span class="badge badge-outline badge-sm">{{ $size }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</section>
