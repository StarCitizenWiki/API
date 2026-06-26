@use('App\Support\Format')
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

    $reputationPrerequisite = data_get($resource, 'reputation_prerequisite');
    $repPrereqLabel = null;

    if ($reputationPrerequisite !== null) {
        $minName = data_get($reputationPrerequisite, 'min_standing.name');
        $maxName = data_get($reputationPrerequisite, 'max_standing.name');

        $standingRange = [$minName, $maxName]
                |> array_filter(...)
                |> array_unique(...)
                |> (static fn($x) => implode(' - ', $x))
                |> trim(...);

        if ($standingRange !== '') {
            $repPrereqLabel = data_get($reputationPrerequisite, 'faction') . ': ' . $standingRange;
        }
    }

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

    $reacceptParts = array_filter([
        $reacceptFailing !== null ? 'Failing: ' . ($reacceptFailing ? 'Yes' : 'No') : null,
        $reacceptAbandoning !== null ? 'Abandoning: ' . ($reacceptAbandoning ? 'Yes' : 'No') : null,
    ]);

    $uuidApiUrl = $uuid !== null ? route('missions.show', $uuid) : null;

    $footer = [
        ['label' => 'UUID', 'value' => $uuid, 'url' => $uuidApiUrl],
        ['label' => 'Version', 'value' => $gameVersion],
    ];

    $columns = [
        [
            'title' => 'Overview',
            'rows' => [
                ['label' => 'Rank', 'value' => $rankIndex],
                ['label' => 'Faction', 'value' => $factionName],
                $repPrereqLabel !== null
                    ? ['label' => 'Rep. Required', 'value' => $repPrereqLabel]
                    : null,
                ['label' => 'Type', 'value' => $rewardScope],
                ['label' => 'Blueprints', 'value' => $hasBlueprints ? 'Yes' : null],
                ['label' => 'Illegal', 'value' => $illegal ? 'Yes' : null],
                ['label' => 'Shareable', 'value' => $shareable ? 'Yes' : null],
                ['label' => 'Prison', 'value' => $availableInPrison ? 'Yes' : null],
                ['label' => 'Once Only', 'value' => $onceOnly ? 'Yes' : null],
            ],
        ],
        [
            'title' => 'Timing',
            'rows' => [
                ['label' => 'Duration', 'value' => $timeToComplete !== null ? Format::valueWithUnit($timeToComplete, 'min', 0) : null],
                ['label' => 'Cooldown', 'value' => $cooldownLabel],
                ['label' => 'Lifetime', 'value' => $lifetimeLabel],
                ['label' => 'Deadline', 'value' => $deadlineMinutes !== null ? Format::valueWithUnit($deadlineMinutes, 'min', 0) : null],
            ],
        ],
        [
            'title' => 'Combat',
            'rows' => [
                $hasCombat !== null
                    ? ['label' => 'Combat', 'value' => $hasCombat ? 'Yes' : 'No']
                    : null,
                $hasDefendObjective !== null
                    ? ['label' => 'Defend', 'value' => $hasDefendObjective ? 'Yes' : 'No']
                    : null,
                $enemyMin !== null || $enemyMax !== null
                    ? ['label' => 'Enemies', 'value' => Format::range($enemyMin, $enemyMax, '', 0)]
                    : null,
                $minCrimeStat !== null || $maxCrimeStat !== null
                    ? ['label' => 'Crime Stat', 'value' => Format::range($minCrimeStat, $maxCrimeStat, '', 0)]
                    : null,
            ],
        ],
        [
            'title' => 'Details',
            'rows' => [
                ['label' => 'Cost', 'value' => $cost !== null ? Format::valueWithUnit($cost, 'aUEC', 0, true) : null],
                ['label' => 'Max Players', 'value' => $maxPlayersPerInstance],
                $starSystems !== []
                    ? ['label' => 'Systems', 'value' => implode(', ', $starSystems)]
                    : null,
                $reacceptParts !== []
                    ? ['label' => 'Reaccept', 'value' => implode(' · ', $reacceptParts)]
                    : null,
            ],
        ],
    ];
@endphp

<x-quick-facts-card :columns="$columns" :footer="$footer" :test-id="'mission-quick-facts-card'" {{ $attributes }} />
