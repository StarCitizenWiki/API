@use('App\Support\Format')
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
                ? Format::valueWithUnit((float) data_get($location, 'jurisdiction.base_fine'), 'aUEC', 0)
                : '-',
        ],
        [
            'label' => 'Stolen Goods Limit',
            'value' => data_get($location, 'jurisdiction.max_stolen_goods_possession_scu') !== null
                ? Format::valueWithUnit((float) data_get($location, 'jurisdiction.max_stolen_goods_possession_scu'), 'SCU', 0)
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
            <div class="breadcrumbs text-sm text-subtle" data-testid="starmap-location-breadcrumbs">
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

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12">
            <x-starmap.locations.hero :location="$location" class="xl:col-span-7" />
            <x-starmap.locations.quick-facts-card
                :location="$location"
                :resolved-version="$resolvedVersion"
                class="xl:col-span-5"
            />
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

                <section class="card card-border bg-base-100 shadow" data-testid="starmap-location-details">
                    <div class="card-body gap-5 p-5 sm:p-6">
                        <x-dl-section dlClass="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($details as $detail)
                                <x-dt-dd :label="$detail['label']">{{ $detail['value'] }}</x-dt-dd>
                            @endforeach
                        </x-dl-section>

                        <div class="space-y-3">
                            <h3 class="font-semibold uppercase text-subtle">Amenities</h3>

                            @if ($amenities !== [])
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($amenities as $amenity)
                                        <span class="badge badge-outline">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-sm text-subtle">No amenities are listed for this location.</div>
                            @endif
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </div>
@endsection
