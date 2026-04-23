@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.app')

@php
    $locationName = data_get($location, 'name', 'Starmap Location');
    $description = data_get($location, 'description');
    $resolvedVersion = request()->query('version');
    $indexRoute = route('web.locations.index');

    if (is_string($resolvedVersion) && $resolvedVersion !== '') {
        $indexRoute = url()->query($indexRoute, ['version' => $resolvedVersion]);
    }

    $viewData = is_array($viewData ?? null) ? $viewData : [];

    $withVersion = static function (string $url) use ($resolvedVersion): string {
        if (! is_string($resolvedVersion) || $resolvedVersion === '') {
            return $url;
        }

        return url()->query($url, ['version' => $resolvedVersion]);
    };

    $locationUuid = data_get($location, 'uuid');
    $locationSlug = data_get($location, 'slug');
    $starName = data_get($location, 'star.name');
    $starSlug = data_get($location, 'star.slug');
    $starUuid = data_get($location, 'star.uuid');
    $parentName = data_get($location, 'parent.name');
    $parentSlug = data_get($location, 'parent.slug');
    $parentUuid = data_get($location, 'parent.uuid');

    $breadcrumbs = data_get($seo ?? [], 'breadcrumbs', []);

    if (! is_array($breadcrumbs) || $breadcrumbs === []) {
        $breadcrumbs = [
            [
                'label' => 'All Locations',
                'url' => $withVersion(route('web.locations.index')),
            ],
        ];

        $ancestorName = null;
        $ancestorIdentifier = null;

        if (
            is_string($starName) && $starName !== ''
            && (is_string($starSlug) || is_string($starUuid)) && $starSlug !== '' && $starUuid !== ''
            && $starUuid !== $locationUuid
        ) {
            $ancestorName = $starName;
            $ancestorIdentifier = $starSlug ?: $starUuid;
        }

        if ($ancestorName !== null && $ancestorIdentifier !== null) {
            $breadcrumbs[] = [
                'label' => $ancestorName,
                'url' => $withVersion(route('web.locations.show', ['identifier' => $ancestorIdentifier])),
            ];
        }

        if (
            is_string($parentName) && $parentName !== ''
            && (is_string($parentSlug) || is_string($parentUuid)) && $parentSlug !== '' && $parentUuid !== ''
            && $parentUuid !== $locationUuid
            && $parentUuid !== $starUuid
        ) {
            $breadcrumbs[] = [
                'label' => $parentName,
                'url' => $withVersion(route('web.locations.show', ['identifier' => $parentSlug ?: $parentUuid])),
            ];
        }

        $breadcrumbs[] = [
            'label' => $locationName,
            'url' => null,
        ];
    }

    $quickFacts = [
        [
            'label' => 'Hierarchy',
            'rows' => [
                [
                    'label' => 'Star',
                    'value' => data_get($location, 'star.name', '-'),
                    'url' => is_string($starUuid) && $starUuid !== '' && $starUuid !== $locationUuid
                        ? $withVersion(route('web.locations.show', ['identifier' => $starSlug ?: $starUuid]))
                        : null,
                    'testid' => 'starmap-location-quick-facts-star-link',
                ],
                [
                    'label' => 'System',
                    'value' => data_get($location, 'system', '-'),
                    'url' => null,
                ],
                [
                    'label' => 'Parent',
                    'value' => data_get($location, 'parent.name', '-'),
                    'url' => is_string($parentUuid) && $parentUuid !== '' && $parentUuid !== $locationUuid
                        ? $withVersion(route('web.locations.show', ['identifier' => $parentSlug ?: $parentUuid]))
                        : null,
                    'testid' => 'starmap-location-quick-facts-parent-link',
                ],
            ],
        ],
        [
            'label' => 'Status',
            'rows' => [
                [
                    'label' => 'Starmap',
                    'value' => data_get($location, 'hide_in_starmap') ? 'Hidden' : 'Visible',
                    'url' => null,
                ],
                [
                    'label' => 'World',
                    'value' => data_get($location, 'hide_in_world') ? 'Hidden' : 'Visible',
                    'url' => null,
                ],
                [
                    'label' => 'Scannable',
                    'value' => data_get($location, 'is_scannable') ? 'Yes' : 'No',
                    'url' => null,
                ],
                [
                    'label' => 'Travel',
                    'value' => data_get($location, 'block_travel') ? 'Blocked' : 'Allowed',
                    'url' => null,
                ],
            ],
        ],
    ];

    $stats = [
        [
            'label' => 'Children',
            'value' => (string) data_get($location, 'child_count', 0),
        ],
        [
            'label' => 'Missions',
            'value' => (string) data_get($location, 'mission_count', 0),
        ],
        [
            'label' => 'Respawn',
            'value' => data_get($location, 'respawn_location_type', '-'),
        ],
        [
            'label' => 'UUID',
            'value' => $locationUuid ?? '-',
        ],
        [
            'label' => 'Version',
            'value' => data_get($location, 'version', '-'),
        ],
    ];

    $details = [
        [
            'label' => 'Type',
            'value' => data_get($location, 'type.name', data_get($location, 'type_name', '-')),
        ],
        [
            'label' => 'Classification',
            'value' => data_get($location, 'type.classification', data_get($location, 'type_classification', '-')),
        ],
        [
            'label' => 'Tag',
            'value' => data_get($location, 'tag.name', '-'),
        ],
        [
            'label' => 'Radar Contact',
            'value' => data_get($location, 'radar_contact_type.display_name', data_get($location, 'radar_contact_type.name', '-')),
        ],
        [
            'label' => 'Affiliation',
            'value' => data_get($location, 'affiliation.name', '-'),
        ],
        [
            'label' => 'Jurisdiction',
            'value' => data_get($location, 'jurisdiction.name', '-'),
        ],
        [
            'label' => 'Prison',
            'value' => data_get($location, 'jurisdiction.is_prison') === null ? '-' : (data_get($location, 'jurisdiction.is_prison') ? 'Yes' : 'No'),
        ],
        [
            'label' => 'Base Fine',
            'value' => data_get($location, 'jurisdiction.base_fine') !== null
                ? fmt_value_with_unit((float) data_get($location, 'jurisdiction.base_fine'), 'aUEC', 0)
                : '-',
        ],
        [
            'label' => 'Stolen Goods Limit',
            'value' => data_get($location, 'jurisdiction.max_stolen_goods_possession_scu') !== null
                ? fmt_value_with_unit((float) data_get($location, 'jurisdiction.max_stolen_goods_possession_scu'), 'SCU', 0)
                : '-',
        ],
    ];

    $amenities = collect(data_get($location, 'amenities', []))
        ->map(static fn (mixed $amenity): ?string => is_array($amenity)
            ? (data_get($amenity, 'display_name') ?: data_get($amenity, 'name'))
            : null)
        ->filter()
        ->values()
        ->all();

    $technicalEntries = array_values(array_filter([
        data_get($location, 'version')
            ? ['label' => 'Game Version', 'value' => data_get($location, 'version'), 'url' => null]
            : null,
        data_get($location, 'updated_at')
            ? ['label' => 'Updated At', 'value' => data_get($location, 'updated_at'), 'url' => null]
            : null,
        data_get($location, 'link')
            ? ['label' => 'API URL', 'value' => data_get($location, 'link'), 'url' => data_get($location, 'link')]
            : null,
    ]));
@endphp

@section('title')
    {!! data_get($seo ?? [], 'title', $pageTitle.' - Star Citizen Starmap Location') !!}
@endsection

@section('meta_description')
    {!! data_get($seo ?? [], 'metaDescription', Str::limit(is_string($description) && trim($description) !== '' ? $description : $locationName, 160)) !!}
@endsection

@section('meta')
    <x-seo.metadata
        :canonical="data_get($seo ?? [], 'canonicalUrl')"
        :keywords="data_get($seo ?? [], 'keywords', [])"
        :og-title="data_get($seo ?? [], 'ogTitle')"
        :og-description="data_get($seo ?? [], 'ogDescription')"
        :twitter-title="data_get($seo ?? [], 'twitterTitle')"
        :twitter-description="data_get($seo ?? [], 'twitterDescription')"
        :structured-data="data_get($seo ?? [], 'structuredData', [])"
    />
@endsection

@section('content')
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-base-content/70" data-testid="starmap-location-breadcrumbs">
                <ul>
                    @foreach ($breadcrumbs as $breadcrumb)
                        <li>
                            @if (! empty($breadcrumb['url']) && ! $loop->last)
                                <a
                                    href="{{ $breadcrumb['url'] }}"
                                    data-testid="{{ $loop->first ? 'starmap-location-breadcrumbs-all-link' : 'starmap-location-breadcrumb-link-'.$loop->index }}"
                                >{{ $breadcrumb['label'] }}</a>
                            @else
                                <span>{{ $breadcrumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <x-resource-search
            title="Search locations"
            description="Find starmap locations by name across the current game version."
            :route="$indexRoute"
            placeholder="Search location names"
            variant="minimal"
        />

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12 xl:items-stretch">
            <x-starmap.locations.hero :location="$location" class="xl:col-span-7" />
            <section class="card h-full border border-base-300 bg-base-100 shadow xl:col-span-5" data-testid="starmap-location-quick-facts">
                <div class="card-body gap-5 p-5 sm:p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-5">
                            @foreach ($quickFacts as $fact)
                                <section class="min-w-0 space-y-3">
                                    <div class="text-sm font-semibold text-base-content/65">
                                        {{ $fact['label'] }}
                                    </div>

                                    <dl class="space-y-2">
                                        @foreach ($fact['rows'] as $row)
                                            <div class="grid grid-cols-2 items-start gap-x-3">
                                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                                    {{ $row['label'] }}
                                                </dt>
                                                <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                                    @if (! empty($row['url']) && $row['value'] !== '-')
                                                        <a
                                                            href="{{ $row['url'] }}"
                                                            class="inline-flex items-center justify-end rounded-sm text-right text-primary underline decoration-primary/45 underline-offset-3 transition hover:text-primary/80 hover:decoration-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20"
                                                            @if (! empty($row['testid'])) data-testid="{{ $row['testid'] }}" @endif
                                                        >{{ $row['value'] }}</a>
                                                    @else
                                                        {{ $row['value'] }}
                                                    @endif
                                                </dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </section>
                            @endforeach
                        </div>

                        <section class="space-y-3 pt-4 md:border-t-0 md:pt-0 md:pl-5">
                            <div class="text-sm font-semibold text-base-content/65">Overview</div>

                            <dl class="space-y-3">
                                @foreach ($stats as $stat)
                                    <div class="grid grid-cols-2 items-start gap-x-3">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                            {{ $stat['label'] }}
                                        </dt>
                                        <dd class="text-right text-sm font-semibold text-base-content">
                                            {{ $stat['value'] }}
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>
                    </div>
                </div>
            </section>
        </div>

        <div class="flex flex-col gap-8">
            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Hierarchy & Navigation</h2>

                <x-starmap.locations.hierarchy-card
                    :child-groups="data_get($viewData, 'childGroups', [])"
                    :child-count="(int) data_get($viewData, 'childCount', 0)"
                    :child-type-count="(int) data_get($viewData, 'childTypeCount', 0)"
                />
            </section>

            @php
                $locationResources = data_get($location, 'resources', []);
                $resourceGroupCount = is_array($locationResources) ? count($locationResources) : 0;
            @endphp

            @if ($resourceGroupCount > 0)
                <section class="space-y-4" data-testid="starmap-location-resources-section">
                    <h2 class="text-lg font-semibold tracking-tight">Resources</h2>

                    <x-starmap.locations.resources-card :resources="$locationResources" />
                    <x-starmap.locations.area-boosts-card :areas="data_get($location, 'area_boosts', [])" />
                </section>
            @endif

            @php
                $locationMissions = data_get($location, 'missions', []);
                $missionGroupCount = is_array($locationMissions) ? count($locationMissions) : 0;
            @endphp

            @if ($missionGroupCount > 0)
                <section class="space-y-4" data-testid="starmap-location-missions-section">
                    <h2 class="text-lg font-semibold tracking-tight">Missions</h2>

                    <x-starmap.locations.missions-card
                        :missions="$locationMissions"
                        :location-uuid="data_get($location, 'uuid')"
                        :total-mission-count="(int) data_get($location, 'mission_count', 0)"
                    />
                </section>
            @endif

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Location Details</h2>

                <section class="card border border-base-300 bg-base-100 shadow" data-testid="starmap-location-details">
                    <div class="card-body gap-5 p-5 sm:p-6">
                        <dl class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($details as $detail)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ $detail['label'] }}</dt>
                                    <dd class="text-sm font-medium text-base-content">{{ $detail['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold text-base-content/75">Amenities</h3>

                            @if ($amenities !== [])
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($amenities as $amenity)
                                        <span class="badge badge-outline">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-sm text-base-content/70">No amenities are listed for this location.</div>
                            @endif
                        </div>
                    </div>
                </section>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Technical</h2>

                <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow" data-testid="starmap-location-technical">
                    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                        Technical
                    </summary>
                    <div class="collapse-content">
                        <dl class="grid gap-4 md:grid-cols-2">
                            @foreach ($technicalEntries as $entry)
                                <div class="space-y-1">
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ $entry['label'] }}</dt>
                                    <dd class="text-sm font-medium text-base-content break-all">
                                        @if ($entry['url'])
                                            <a href="{{ $entry['url'] }}" class="link link-primary">{{ $entry['value'] }}</a>
                                        @else
                                            {{ $entry['value'] }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </details>
            </section>
        </div>
    </div>
@endsection
