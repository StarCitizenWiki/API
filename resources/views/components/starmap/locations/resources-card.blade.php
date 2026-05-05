@props(['resources'])

@php
    $resolvedVersion = request()->query('version');

    $withVersion = static function (string $url) use ($resolvedVersion): string {
        if (! is_string($resolvedVersion) || $resolvedVersion === '') {
            return $url;
        }

        return url()->query($url, ['version' => $resolvedVersion]);
    };

    $groups = collect($resources)->sortBy('mining_type_sort_order');
    $groupCount = $groups->count();
@endphp

@if ($groups->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'flex flex-col gap-4']) }} data-testid="starmap-location-resources">
        @foreach ($groups as $group)
            @php
                $groupName = data_get($group, 'group_name', 'Unknown');
                $groupLabel = data_get($group, 'mining_type', $groupName);
                $resourceKind = data_get($group, 'resource_kind', '');
                $groupResources = collect(data_get($group, 'resources', []));
                $groupProbMin = data_get($group, 'group_probability_min_percent');
                $groupProbMax = data_get($group, 'group_probability_max_percent');
            @endphp

            <details class="collapse collapse-arrow card card-border bg-base-100 shadow" @if($groupCount <= 2) open @endif data-testid="starmap-location-resource-group">
                <summary class="collapse-title min-h-11 text-sm font-semibold flex items-center gap-3">
                    {{ $groupLabel }}
                    @if ($groupProbMin !== null)
                        <span class="badge badge-outline badge-sm" title="Group spawn probability">{{ $groupProbMin === $groupProbMax ? "{$groupProbMin}%" : "{$groupProbMin}-{$groupProbMax}%" }}</span>
                    @endif
                </summary>

                <div class="collapse-content px-5 sm:px-6 pb-5 sm:pb-6">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        @foreach ($groupResources as $resource)
                            @php
                                $resourceName = data_get($resource, 'name');
                                $resourceTier = data_get($resource, 'tier');
                                $resourceWebUrl = data_get($resource, 'web_url');
                            @endphp

                            <section class="card border border-base-200 bg-base-100/50" data-testid="starmap-location-resource-card">
                                <div class="card-body gap-4 p-4 sm:p-5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($resourceWebUrl)
                                            <a href="{{ $withVersion($resourceWebUrl) }}" class="text-base font-semibold tracking-tight link link-hover link-primary">
                                                {{ $resourceName }}
                                            </a>
                                        @else
                                            <h4 class="text-base font-semibold tracking-tight">{{ $resourceName }}</h4>
                                        @endif

                                        @if ($resourceTier)
                                            <x-resources.rarity-badge :tier="$resourceTier" />
                                        @endif
                                    </div>

                                    <x-resources.deposit-detail :deposit="$resource" :resource-kind="$resourceKind" :with-version="$withVersion" />
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            </details>
        @endforeach
    </div>
@else
    <div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <div class="text-sm text-subtle">No resource data available for this location.</div>
        </div>
    </div>
@endif
