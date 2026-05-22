@props(['resource'])

@php
    $title = data_get($resource, 'title', 'Mission');
    $description = data_get($resource, 'description');
    $faction = data_get($resource, 'faction');
    $factionName = data_get($faction, 'name');
    $rewardScope = data_get($resource, 'reward_scope');

    $version = request()->query('version');
    $makeMissionsUrl = static function (array $filters) use ($version): string {
        $url = route('web.missions.index', ['filter' => $filters]);

        if (is_string($version) && $version !== '') {
            $url = url()->query($url, ['version' => $version]);
        }

        return $url;
    };

    $headlineLinks = array_values(array_filter([
        $factionName ? ['label' => $factionName, 'url' => $makeMissionsUrl(['faction' => $factionName])] : null,
        $rewardScope ? ['label' => $rewardScope, 'url' => $makeMissionsUrl(['reward_scope' => $rewardScope])] : null,
    ]));

    $iconName = match ($rewardScope) {
        'Bounty Hunter' => 'crosshair',
        'Assassination' => 'skull',
        'Hauling' => 'truck',
        'Mining' => 'pickaxe',
        'Salvage' => 'wrench',
        'Security' => 'shield',
        'Investigation' => 'search',
        'Recovery' => 'locate',
        default => 'flag',
    };

    $notForRelease = data_get($resource, 'not_for_release');
    $workInProgress = data_get($resource, 'work_in_progress');
    $wikiUrl = 'https://starcitizen.tools/' . str_replace(' ', '_', $title);
@endphp

<section {{ $attributes->merge(['class' => 'card bg-base-100 shadow', 'data-testid' => 'mission-hero']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-accent/10 text-muted sm:size-11"
                        aria-label="Mission type"
                    >
                        <x-icon :name="$iconName" class="size-5 sm:size-6" />
                    </span>

                    <h1 class="min-w-0 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $title }}
                    </h1>

                    @if ($notForRelease || $workInProgress)
                        <span class="badge {{ $workInProgress ? 'badge-error' : 'badge-warning' }} badge-sm shrink-0 font-semibold">
                            {{ $workInProgress ? 'WIP' : 'Unreleased' }}
                        </span>
                    @endif
                </div>

                @if ($headlineLinks !== [])
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-subtle">
                        @foreach ($headlineLinks as $entry)
                            @if (! $loop->first)
                                <span aria-hidden="true" class="text-base-content/35">|</span>
                            @endif

                            @if ($entry['url'])
                                <a href="{{ $entry['url'] }}" class="link link-hover font-semibold text-subtle">
                                    {{ $entry['label'] }}
                                </a>
                            @else
                                <span>{{ $entry['label'] }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            @php
                $rankIndex = data_get($resource, 'rank_index');
            @endphp

            @if ($rankIndex !== null)
                <div class="shrink-0 text-right">
                    <div class="text-xs text-subtle uppercase tracking-wide">
                        Rank
                    </div>
                    <div class="text-lg font-bold">
                        {{ $rankIndex }}
                    </div>
                </div>
            @endif
        </div>

        @if ($description)
            <div class="max-h-48 max-w-3xl overflow-y-auto text-sm leading-6 whitespace-pre-line text-subtle sm:text-base" data-testid="mission-hero-description">
                {!! nl2br(e($description)) !!}
            </div>
        @endif
        <div class="card-actions justify-end pt-4">
            <span class="text-xs text-muted font-semibold">Find on</span>
            <a href="{{ $wikiUrl }}" class="link link-hover link-primary text-xs" target="_blank" rel="noopener noreferrer">starcitizen.tools</a>
        </div>
    </div>
</section>
