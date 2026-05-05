@props([
    'missions',
    'locationUuid',
    'totalMissionCount' => 0,
])

@php
    $resolvedVersion = request()->query('version');

    $withVersion = static function (string $url) use ($resolvedVersion): string {
        if (! is_string($resolvedVersion) || $resolvedVersion === '') {
            return $url;
        }

        return url()->query($url, ['version' => $resolvedVersion]);
    };

    $missionsIndexUrl = $withVersion(route('web.missions.index') . '?filter[location]=' . $locationUuid);

    $groups = collect($missions)
        ->filter(fn (array $group): bool => in_array(data_get($group, 'purpose'), ['availability', 'Destination']));
    $groupCount = $groups->count();

    $purposeLabel = static function (string $purpose): string {
        return match ($purpose) {
            'availability' => 'Available at Location',
            'Destination' => 'Destination',
            default => $purpose,
        };
    };
@endphp

<div class="flex flex-col gap-4" data-testid="starmap-location-missions">
    @foreach ($groups as $group)
        @php
            $purpose = data_get($group, 'purpose', 'Unknown');
            $label = $purposeLabel($purpose);
            $groupMissions = collect(data_get($group, 'missions', []));
            $groupMissionCount = $groupMissions->count();
            $dedupedMissions = $groupMissions
                ->groupBy(fn (array $mission): string => data_get($mission, 'title') ?? '__null__')
                ->map(fn (\Illuminate\Support\Collection $duplicates): array => [
                    ...$duplicates->first(),
                    '_dedupe_count' => $duplicates->count(),
                ])
                ->values();
            $visibleMissions = $dedupedMissions->take(9);
        @endphp

        <details class="collapse collapse-arrow card card-border bg-base-100 shadow" @if($groupCount <= 3) open @endif data-testid="starmap-location-mission-group">
            <summary class="collapse-title min-h-11 text-sm font-semibold flex items-center gap-3">
                {{ $label }}
                <span class="badge badge-outline badge-sm">{{ $groupMissionCount }}</span>
            </summary>

            <div class="collapse-content px-5 sm:px-6 pb-5 sm:pb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach ($visibleMissions as $mission)
                        @php
                            $missionTitle = data_get($mission, 'title', 'Unknown mission');
                            $missionType = data_get($mission, 'mission_type');
                            $missionIllegal = data_get($mission, 'illegal', false);
                            $missionCombat = data_get($mission, 'has_combat', false);
                            $factionName = data_get($mission, 'faction.name');
                            $dedupeCount = (int) data_get($mission, '_dedupe_count', 1);
                            $missionUrl = data_get($mission, 'web_url') ?: $missionsIndexUrl;
                        @endphp

                        <a
                            href="{{ $missionUrl }}"
                            class="group block rounded-box border border-base-300 bg-base-100 p-3 transition hover:border-base-content/20 hover:bg-base-200/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-base-content/20"
                            data-testid="starmap-location-mission-card"
                        >
                            <div class="min-w-0 space-y-1.5">
                                <div class="flex items-center gap-1.5 truncate text-sm font-semibold text-base-content transition group-hover:text-base-content/80">
                                    <span class="truncate">{{ $missionTitle }}</span>
                                    @if ($dedupeCount > 1)
                                        <span class="badge badge-ghost badge-sm shrink-0">x{{ $dedupeCount }}</span>
                                    @endif
                                </div>

                                @if ($missionType || $factionName)
                                    <div class="truncate text-xs text-subtle">
                                        @if ($missionType)
                                            {{ $missionType }}
                                        @endif
                                        @if ($missionType && $factionName)
                                            <span class="text-base-content/35">·</span>
                                        @endif
                                        @if ($factionName)
                                            {{ $factionName }}
                                        @endif
                                    </div>
                                @endif

                                <div class="flex flex-wrap gap-1.5">
                                    @if ($missionIllegal)
                                        <span class="badge badge-warning badge-sm">Illegal</span>
                                    @endif
                                    @if ($missionCombat)
                                        <span class="badge badge-error badge-sm">Combat</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>


            </div>
        </details>
    @endforeach

    @if ($totalMissionCount > 0)
        <a
            href="{{ $missionsIndexUrl }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:text-primary/80 transition"
            data-testid="starmap-location-missions-view-all"
        >
            View all {{ $totalMissionCount }} missions
            <x-icon name="arrow-right" class="size-4" />
        </a>
    @endif
</div>
