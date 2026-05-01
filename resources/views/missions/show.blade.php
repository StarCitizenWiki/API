@php
    use Illuminate\Support\Str;

    $title = data_get($resource, 'title', 'Mission');
    $seoBreadcrumbs = data_get($seo, 'breadcrumbs', []);

    $indexRoute = $seoBreadcrumbs[0]['url'] ?? route('web.missions.index');
    $viewBreadcrumbs = $seoBreadcrumbs;
    if ($viewBreadcrumbs === []) {
        $viewBreadcrumbs = [
            ['label' => 'All Missions', 'url' => $indexRoute],
            ['label' => $title, 'url' => null],
        ];
    } else {
        $lastIndex = count($viewBreadcrumbs) - 1;
        $viewBreadcrumbs[$lastIndex]['url'] = null;
    }

    $rewardItems = data_get($resource, 'reward_items') ?? [];
    $blueprints = data_get($resource, 'blueprints');
    $blueprintItems = data_get($blueprints, 'items') ?? [];
    $reputationGained = data_get($resource, 'reputation_gained') ?? [];
    $reputationLost = data_get($resource, 'reputation_lost') ?? [];
    $combat = data_get($resource, 'combat');
    $entitySpawns = data_get($resource, 'entity_spawns') ?? [];
    $haulingOrders = data_get($resource, 'hauling_orders') ?? [];
    $mergedLocations = data_get($resource, 'merged_locations') ?? [];

    $purposeHelpText = [
        'Destinations' => 'Where you need to go to complete this mission',
        'Locations' => 'Locations relevant to this mission',
        'Availability' => 'Where this mission can be accepted',
    ];

    $hasRewards = data_get($resource, 'has_rewards', false);
    $hasCombatSection = data_get($resource, 'has_combat_section', false);
    $hasLocations = data_get($resource, 'has_locations', false);

    $technicalEntries = array_values(array_filter([
        ['label' => 'Mission Type', 'value' => data_get($resource, 'mission_type') ?? '-', 'url' => null],
        ['label' => 'Mission Giver', 'value' => data_get($resource, 'mission_giver') ?? '-', 'url' => null],
        data_get($resource, 'debug_name') ? ['label' => 'Debug Name', 'value' => data_get($resource, 'debug_name'), 'url' => null] : null,
    ]));
    $completionTags = data_get($resource, 'completion_tags') ?? [];
@endphp
@extends('layouts.app')

@section('title')
    {!! data_get($seo, 'title', $pageTitle.' - Star Citizen Mission') !!}
@endsection
@section('meta_description')
    {!! data_get($seo, 'metaDescription', Str::limit(data_get($resource, 'description', 'Star Citizen mission details.'), 160)) !!}
@endsection

@section('meta')
    <x-seo.metadata
        :canonical="data_get($seo, 'canonicalUrl')"
        :keywords="data_get($seo, 'keywords', [])"
        :og-title="data_get($seo, 'ogTitle')"
        :og-description="data_get($seo, 'ogDescription')"
        :twitter-title="data_get($seo, 'twitterTitle')"
        :twitter-description="data_get($seo, 'twitterDescription')"
        :structured-data="data_get($seo, 'structuredData', [])"
    />
@endsection

@section('content')
    <div class="flex flex-col gap-4">
        <div class="breadcrumbs text-sm text-base-content/70" data-testid="mission-breadcrumbs">
            <ul>
                @foreach ($viewBreadcrumbs as $breadcrumb)
                    <li>
                        @if (! empty($breadcrumb['url']))
                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                        @else
                            <span>{{ $breadcrumb['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <x-resource-search
            title="Search missions"
            description="Find missions by title across the current game version."
            :route="$indexRoute"
            placeholder="Search mission titles"
            variant="minimal"
            apiEndpoint="/api/missions"
        />

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12 xl:items-stretch">
            <x-missions.hero :resource="$resource" class="xl:col-span-7" />
            <x-missions.quick-facts-card :resource="$resource" class="xl:col-span-5" />
        </div>

        <div class="flex flex-col gap-8">
            @if ($hasRewards)
                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold tracking-tight">Rewards &amp; Reputation</h2>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @if ($rewardItems !== [])
                            <div class="card border border-base-300 bg-base-100 shadow">
                                <div class="card-body p-5 sm:p-6">
                                    <h3 class="text-sm font-semibold text-base-content/65 mb-3">Reward Items</h3>
                                    <div class="overflow-x-auto">
                                        <table class="table table-sm table-zebra">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Amount</th>
                                                    <th>Home</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($rewardItems as $item)
                                                    <tr>
                                                        <td>
                                                            @if (data_get($item, 'web_link'))
                                                                <a href="{{ data_get($item, 'web_link') }}" class="link link-primary">{{ data_get($item, 'name', '-') }}</a>
                                                            @else
                                                                {{ data_get($item, 'name', '-') }}
                                                            @endif
                                                        </td>
                                                        <td>{{ data_get($item, 'amount') ?? '-' }}</td>
                                                        <td>{{ data_get($item, 'send_to_home') === true ? 'Yes' : '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($blueprintItems !== [])
                            <div class="card border border-base-300 bg-base-100 shadow">
                                <div class="card-body p-5 sm:p-6">
                                    <h3 class="text-sm font-semibold text-base-content/65 mb-3">Blueprints</h3>
                                    @if (data_get($blueprints, 'drop_chance'))
                                        <p class="text-xs text-base-content/60 mb-3">
                                            One blueprint from this pool ({{ data_get($blueprints, 'drop_chance_percent') }}% drop chance)
                                        </p>
                                    @endif
                                    <div class="overflow-x-auto">
                                        <table class="table table-sm table-zebra">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Blueprint</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($blueprintItems as $bpItem)
                                                    <tr>
                                                        <td>
                                                            @if (data_get($bpItem, 'web_item_link'))
                                                                <a href="{{ data_get($bpItem, 'web_item_link') }}" class="link link-primary">{{ data_get($bpItem, 'name', '-') }}</a>
                                                            @else
                                                                {{ data_get($bpItem, 'name', '-') }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (data_get($bpItem, 'web_blueprint_link'))
                                                                <a href="{{ data_get($bpItem, 'web_blueprint_link') }}" class="link link-primary text-xs">View</a>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($reputationGained !== [] || $reputationLost !== [])
                            <div class="card border border-base-300 bg-base-100 shadow">
                                <div class="card-body p-5 sm:p-6">
                                    <div class="space-y-6">
                                        @if ($reputationGained !== [])
                                            <div>
                                                <h3 class="text-sm font-semibold text-base-content/65 mb-3">Reputation Gained</h3>
                                                <div class="overflow-x-auto">
                                                    <table class="table table-sm table-zebra">
                                                        <thead>
                                                            <tr>
                                                                <th>Faction</th>
                                                                <th>Amount</th>
                                                                <th>Scope</th>
                                                                <th>Tier</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($reputationGained as $rep)
                                                                <tr>
                                                                    <td>{{ data_get($rep, 'faction') ?? '-' }}</td>
                                                                    <td>
                                                                        @if (data_get($rep, 'amount'))
                                                                            <span class="text-success">+{{ data_get($rep, 'amount') }}</span>
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ data_get($rep, 'scope') ?? '-' }}</td>
                                                                    <td>{{ data_get($rep, 'tier') ?? '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($reputationLost !== [])
                                            <div>
                                                <h3 class="text-sm font-semibold text-base-content/65 mb-3">Reputation Lost</h3>
                                                <div class="overflow-x-auto">
                                                    <table class="table table-sm table-zebra">
                                                        <thead>
                                                            <tr>
                                                                <th>Faction</th>
                                                                <th>Amount</th>
                                                                <th>Scope</th>
                                                                <th>Tier</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($reputationLost as $rep)
                                                                <tr>
                                                                    <td>{{ data_get($rep, 'faction') ?? '-' }}</td>
                                                                    <td>
                                                                        @if (data_get($rep, 'amount'))
                                                                            <span class="text-error">{{ data_get($rep, 'amount') }}</span>
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ data_get($rep, 'scope') ?? '-' }}</td>
                                                                    <td>{{ data_get($rep, 'tier') ?? '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            <x-missions.chain-flow :resource="$resource" />

            @if ($haulingOrders !== [])
                <x-missions.hauling-section :hauling-orders="$haulingOrders" />
            @endif

            @if ($hasCombatSection)
                <x-missions.combat-card :resource="$resource" />
            @endif

            @if (data_get($resource, 'faction') !== null)
                @php
                    $factionData = data_get($resource, 'faction');
                    $reputationLadder = data_get($factionData, 'reputation_ladder');
                    $reputationLadderStandings = data_get($reputationLadder, 'standings') ?? [];
                    $hasLadder = $reputationLadder !== null && $reputationLadderStandings !== [];
                @endphp

                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold tracking-tight">Faction</h2>
                        <span class="text-base text-base-content/70">{{ data_get($factionData, 'name') }}</span>
                    </div>

                    <div class="card border border-base-300 bg-base-100 shadow">
                        <div class="card-body p-5 sm:p-6">
                            <div class="grid gap-6 {{ $hasLadder ? 'lg:grid-cols-2' : '' }}">
                                <div>
                                    <h3 class="text-sm font-semibold text-base-content/65 mb-3">Overview</h3>
                                    <dl class="space-y-2">
                                        @foreach ([
                                            ['label' => 'Type', 'value' => data_get($factionData, 'faction_type')],
                                            ['label' => 'Headquarters', 'value' => data_get($factionData, 'headquarters')],
                                            ['label' => 'Area', 'value' => data_get($factionData, 'area')],
                                            ['label' => 'Focus', 'value' => data_get($factionData, 'focus')],
                                            ['label' => 'Founded', 'value' => data_get($factionData, 'founded')],
                                            ['label' => 'Leadership', 'value' => data_get($factionData, 'leadership')],
                                        ] as $row)
                                            @if ($row['value'] !== null)
                                                <div class="grid grid-cols-2 items-start gap-x-3">
                                                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">{{ $row['label'] }}</dt>
                                                    <dd class="text-right text-sm font-semibold text-base-content">{{ $row['value'] }}</dd>
                                                </div>
                                            @endif
                                        @endforeach
                                    </dl>
                                </div>

                                @if ($hasLadder)
                                    <div>
                                        <h3 class="text-sm font-semibold text-base-content/65 mb-3">{{ data_get($reputationLadder, 'scope_name', 'Reputation') }}</h3>
                                        <div class="overflow-x-auto">
                                            <table class="table table-sm table-zebra">
                                                <thead>
                                                    <tr>
                                                        <th>Rank</th>
                                                        <th>XP Required</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($reputationLadderStandings as $standing)
                                                        <tr>
                                                            <td>{{ data_get($standing, 'display_name') ?? data_get($standing, 'name', '-') }}</td>
                                                            <td>{{ number_format(data_get($standing, 'min_reputation', 0)) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if ($hasLocations)
                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold tracking-tight">Locations</h2>
                        @php
                            $totalLocations = array_sum(array_map(fn (array $locs): int => count($locs), $mergedLocations));
                        @endphp
                        <span class="badge badge-ghost badge-sm">{{ $totalLocations }} locations</span>
                    </div>

                    @foreach ($mergedLocations as $groupLabel => $locations)
                        @php
                            $groupCount = count($locations);
                            $visibleLocations = array_slice($locations, 0, 9);
                            $hiddenLocations = array_slice($locations, 9);
                            $hiddenCount = count($hiddenLocations);
                            $startOpen = $groupLabel !== 'Availability';
                        @endphp

                        <details class="collapse collapse-arrow card border border-base-300 bg-base-100 shadow" @if ($startOpen) open @endif data-testid="mission-location-group-{{ Str::slug($groupLabel) }}">
                            <summary class="collapse-title min-h-11 text-sm font-semibold flex items-center gap-3">
                                {{ $groupLabel }}
                                <span class="badge badge-outline badge-sm">{{ $groupCount }}</span>
                                @if (isset($purposeHelpText[$groupLabel]))
                                    <span class="text-xs text-base-content/50">{{ $purposeHelpText[$groupLabel] }}</span>
                                @endif
                            </summary>

                            <div class="collapse-content px-5 sm:px-6 pb-5 sm:pb-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                                    @foreach ($visibleLocations as $location)
                                        <a
                                            href="{{ data_get($location, 'web_link', '#') }}"
                                            class="group block rounded-box border border-base-300 bg-base-100/70 p-3 transition hover:border-base-content/20 hover:bg-base-200/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-base-content/20"
                                        >
                                            <div class="min-w-0 space-y-1.5">
                                                <div class="flex items-center gap-1.5 truncate text-sm font-semibold text-base-content transition group-hover:text-base-content/80">
                                                    <span class="truncate">{{ data_get($location, 'name', '-') }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5 text-xs text-base-content/60">
                                                    @if (data_get($location, 'system'))
                                                        <span class="badge badge-ghost badge-sm">{{ data_get($location, 'system') }}</span>
                                                    @endif
                                                    @if (data_get($location, 'type'))
                                                        <span>{{ data_get($location, 'type') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>

                                @if ($hiddenCount > 0)
                                    <details class="mt-4">
                                        <summary class="text-sm font-medium text-primary hover:text-primary/80 cursor-pointer transition">
                                            Show {{ $hiddenCount }} more {{ Str::lower($groupLabel) }}
                                        </summary>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 mt-3">
                                            @foreach ($hiddenLocations as $location)
                                                <a
                                                    href="{{ data_get($location, 'web_link', '#') }}"
                                                    class="group block rounded-box border border-base-300 bg-base-100/70 p-3 transition hover:border-base-content/20 hover:bg-base-200/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-base-content/20"
                                                >
                                                    <div class="min-w-0 space-y-1.5">
                                                        <div class="flex items-center gap-1.5 truncate text-sm font-semibold text-base-content transition group-hover:text-base-content/80">
                                                            <span class="truncate">{{ data_get($location, 'name', '-') }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 text-xs text-base-content/60">
                                                            @if (data_get($location, 'system'))
                                                                <span class="badge badge-ghost badge-sm">{{ data_get($location, 'system') }}</span>
                                                            @endif
                                                            @if (data_get($location, 'type'))
                                                                <span>{{ data_get($location, 'type') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </section>
            @endif

            <x-technical-section :entries="$technicalEntries" testId="mission-technical-card">
                @if ($completionTags !== [])
                    <div class="mt-5 pt-5 border-t border-base-300">
                        <h3 class="text-sm font-semibold text-base-content/65 mb-3">Completion Tags</h3>
                        <div class="overflow-x-auto">
                            <table class="table table-sm table-zebra">
                                <thead>
                                    <tr>
                                        <th>Tag</th>
                                        <th>Unlocks Missions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($completionTags as $tag)
                                        <tr>
                                            <td>{{ data_get($tag, 'name', '-') }}</td>
                                            <td>
                                                @php
                                                    $tagMissions = data_get($tag, 'unlocks_missions', []);
                                                @endphp
                                                @if ($tagMissions !== [])
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach ($tagMissions as $tagMission)
                                                            @if (data_get($tagMission, 'link'))
                                                                <a href="{{ data_get($tagMission, 'link') }}" class="link link-primary text-sm">{{ data_get($tagMission, 'title', '-') }}</a>
                                                            @else
                                                                <span class="text-sm">{{ data_get($tagMission, 'title', '-') }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </x-technical-section>
        </div>
    </div>
@endsection
