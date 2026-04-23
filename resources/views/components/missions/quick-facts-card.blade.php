@props(['resource'])

@php
    $uuid = data_get($resource, 'uuid');
    $rankIndex = data_get($resource, 'rank_index');
    $faction = data_get($resource, 'faction');
    $factionName = data_get($faction, 'name');
    $rewardScope = data_get($resource, 'reward_scope');
    $hasBlueprints = data_get($resource, 'blueprints') !== null;

    $timeToComplete = data_get($resource, 'time_to_complete_minutes');
    $cooldown = data_get($resource, 'cooldown');
    $cooldownLabel = data_get($cooldown, 'label');
    $lifetime = data_get($resource, 'lifetime');
    $lifetimeLabel = data_get($lifetime, 'label');
    $deadline = data_get($resource, 'deadline');
    $deadlineMinutes = data_get($deadline, 'completion_time_minutes');

    $hasCombat = data_get($resource, 'has_combat');
    $hasDefendObjective = data_get($resource, 'has_defend_objective');
    $enemyMin = data_get($resource, 'enemy_count_min');
    $enemyMax = data_get($resource, 'enemy_count_max');
    $minCrimeStat = data_get($resource, 'min_crime_stat');
    $maxCrimeStat = data_get($resource, 'max_crime_stat');

    $cost = data_get($resource, 'cost');
    $maxPlayersPerInstance = data_get($resource, 'max_players_per_instance');
    $reacceptFailing = data_get($resource, 'reaccept_after_failing');
    $reacceptAbandoning = data_get($resource, 'reaccept_after_abandoning');
    $starSystems = data_get($resource, 'star_systems') ?? [];
    $gameVersion = data_get($resource, 'game_version');

    $illegal = data_get($resource, 'illegal');
    $shareable = data_get($resource, 'shareable');
    $availableInPrison = data_get($resource, 'available_in_prison');
    $onceOnly = data_get($resource, 'once_only');

    $reacceptParts = array_values(array_filter([
        $reacceptFailing !== null ? 'Failing: ' . ($reacceptFailing ? 'Yes' : 'No') : null,
        $reacceptAbandoning !== null ? 'Abandoning: ' . ($reacceptAbandoning ? 'Yes' : 'No') : null,
    ]));

    $columns = [
        [
            [
                'title' => 'Overview',
                'rows' => array_values(array_filter([
                    $rankIndex !== null
                        ? ['label' => 'Rank', 'value' => (string) $rankIndex]
                        : null,
                    $factionName !== null
                        ? ['label' => 'Faction', 'value' => $factionName]
                        : null,
                    $rewardScope !== null
                        ? ['label' => 'Type', 'value' => $rewardScope]
                        : null,
                    $hasBlueprints
                        ? ['label' => 'Blueprints', 'value' => 'Yes']
                        : null,
                    $illegal
                        ? ['label' => 'Illegal', 'value' => 'Yes']
                        : null,
                    $shareable
                        ? ['label' => 'Shareable', 'value' => 'Yes']
                        : null,
                    $availableInPrison
                        ? ['label' => 'Prison', 'value' => 'Yes']
                        : null,
                    $onceOnly
                        ? ['label' => 'Once Only', 'value' => 'Yes']
                        : null,
                ])),
            ],
            [
                'title' => 'Timing',
                'rows' => array_values(array_filter([
                    $timeToComplete !== null
                        ? ['label' => 'Duration', 'value' => fmt_value_with_unit($timeToComplete, 'min', 0)]
                        : null,
                    $cooldownLabel !== null
                        ? ['label' => 'Cooldown', 'value' => $cooldownLabel]
                        : null,
                    $lifetimeLabel !== null
                        ? ['label' => 'Lifetime', 'value' => $lifetimeLabel]
                        : null,
                    $deadlineMinutes !== null
                        ? ['label' => 'Deadline', 'value' => fmt_value_with_unit($deadlineMinutes, 'min', 0)]
                        : null,
                ])),
            ],
        ],
        [
            [
                'title' => 'Combat',
                'rows' => array_values(array_filter([
                    $hasCombat !== null
                        ? ['label' => 'Combat', 'value' => $hasCombat ? 'Yes' : 'No']
                        : null,
                    $hasDefendObjective !== null
                        ? ['label' => 'Defend', 'value' => $hasDefendObjective ? 'Yes' : 'No']
                        : null,
                    $enemyMin !== null || $enemyMax !== null
                        ? ['label' => 'Enemies', 'value' => fmt_range($enemyMin, $enemyMax, '', 0)]
                        : null,
                    $minCrimeStat !== null || $maxCrimeStat !== null
                        ? ['label' => 'Crime Stat', 'value' => fmt_range($minCrimeStat, $maxCrimeStat, '', 0)]
                        : null,
                ])),
            ],
            [
                'title' => 'Details',
                'rows' => array_values(array_filter([
                    $cost !== null
                        ? ['label' => 'Cost', 'value' => fmt_value_with_unit($cost, 'aUEC', 0, true)]
                        : null,
                    $maxPlayersPerInstance !== null
                        ? ['label' => 'Max Players', 'value' => (string) $maxPlayersPerInstance]
                        : null,
                    $starSystems !== []
                        ? ['label' => 'Systems', 'value' => implode(', ', $starSystems)]
                        : null,
                    $reacceptParts !== []
                        ? ['label' => 'Reaccept', 'value' => implode(' · ', $reacceptParts)]
                        : null,
                    $uuid !== null
                        ? ['label' => 'UUID', 'value' => $uuid]
                        : null,
                    $gameVersion !== null
                        ? ['label' => 'Version', 'value' => $gameVersion]
                        : null,
                ])),
            ],
        ],
    ];
@endphp

<section {{ $attributes->merge(['class' => 'card h-full border border-base-300 bg-base-100 shadow', 'data-testid' => 'mission-quick-facts-card']) }}>
    <div class="card-body p-5 sm:p-6">
        <div class="grid h-full gap-6 sm:grid-cols-2 sm:gap-8">
            @foreach ($columns as $column)
                <div class="space-y-6">
                    @foreach ($column as $section)
                        <section class="min-w-0 space-y-3">
                            <div class="text-sm font-semibold text-base-content/65">
                                {{ $section['title'] }}
                            </div>

                            @if ($section['rows'] !== [])
                                <dl class="space-y-2">
                                    @foreach ($section['rows'] as $row)
                                        <div class="grid grid-cols-2 items-start gap-x-3">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                                {{ $row['label'] }}
                                            </dt>
                                            <dd class="text-right text-sm font-semibold text-base-content">
                                                {{ $row['value'] }}
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @else
                                <div class="text-sm text-base-content/70">-</div>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>
