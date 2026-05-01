@props(['resource'])

@php
    $systemsGrouped = data_get($resource, 'systems_grouped', []);
    $resolvedVersion = request()->query('version');

    $withVersion = static function (string $url) use ($resolvedVersion): string {
        if (! is_string($resolvedVersion) || $resolvedVersion === '') {
            return $url;
        }

        return url()->query($url, ['version' => $resolvedVersion]);
    };
@endphp

@if ($systemsGrouped !== [])
    <div class="flex flex-col gap-8">
        @foreach ($systemsGrouped as $systemGroup)
            @php
                $systemName = data_get($systemGroup, 'name', 'Unknown System');
                $systemLocations = data_get($systemGroup, 'locations', []);
            @endphp

            <section class="space-y-4" data-testid="resource-system">
                <h3 class="flex items-center gap-2 text-lg font-semibold tracking-tight">
                    {{ $systemName }}
                    <span class="badge badge-ghost badge-sm">{{ count($systemLocations) }}</span>
                </h3>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    @foreach ($systemLocations as $location)
                        @php
                            $locationName = data_get($location, 'name');
                            $locationType = data_get($location, 'type');
                            $locationUuid = data_get($location, 'uuid');
                            $designation = data_get($location, 'designation');
                            $parentName = data_get($location, 'parent_name');
                            $parentType = data_get($location, 'parent_type');
                            $parentUuid = data_get($location, 'parent_uuid');
                            $groupProb = data_get($location, 'group_probability_percent');
                            $relProb = data_get($location, 'relative_probability_percent');
                            $deposits = data_get($location, 'resources', []);

                            $locationShowUrl = $locationUuid
                                ? $withVersion(route('web.locations.show', ['identifier' => $locationUuid]))
                                : null;

                            $displayTitle = data_get($location, 'display_name', $locationName);
                            $showParent = $parentName && in_array($parentType, ['Planet', 'Moon']);
                            $parentShowUrl = $parentUuid
                                ? $withVersion(route('web.locations.show', ['identifier' => $parentUuid]))
                                : null;
                        @endphp

                        <section class="card border border-base-300 bg-base-100 shadow" data-testid="resource-location-card">
                            <div class="card-body gap-4 p-5 sm:p-6">
                                <div class="flex flex-wrap items-center gap-3">
                                    @if ($locationShowUrl)
                                        <a href="{{ $locationShowUrl }}" class="text-lg font-semibold tracking-tight link link-hover link-primary">
                                            {{ $displayTitle }}
                                        </a>
                                    @else
                                        <h5 class="text-lg font-semibold tracking-tight">{{ $displayTitle }}</h5>
                                    @endif

                                    @if ($showParent)
                                        @if ($parentShowUrl)
                                            <a href="{{ $parentShowUrl }}" class="badge badge-outline badge-sm link link-hover">{{ $parentName }}</a>
                                        @else
                                            <span class="badge badge-outline badge-sm">{{ $parentName }}</span>
                                        @endif
                                    @endif

                                    @if ($locationType)
                                        <span class="badge badge-outline badge-sm">{{ $locationType }}</span>
                                    @endif

                                    @if ($groupProb !== null)
                                        <span class="badge badge-ghost badge-sm" title="Probability of this group spawning at this location">Spawn: {{ $groupProb }}%</span>
                                    @endif

                                    @if ($relProb !== null)
                                        <span class="badge badge-ghost badge-sm" title="Relative probability of this resource vs. others in the group">Occurrence: {{ $relProb }}%</span>
                                    @endif
                                </div>

                                @foreach ($deposits as $deposit)
                                    <x-resources.deposit-detail :deposit="$deposit" :resource-kind="data_get($deposit, 'resource_kind', '')" :with-version="$withVersion" />
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@else
    <div class="card border border-base-300 bg-base-100 shadow">
        <div class="card-body p-5 sm:p-6">
            <div class="text-sm text-subtle">No location data available for this resource.</div>
        </div>
    </div>
@endif
