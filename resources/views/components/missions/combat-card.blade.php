@use('App\Support\Format')
@props(['resource'])

@php
    $combat = data_get($resource, 'combat');
    $combatSummary = data_get($combat, 'summary');
    $combatByGroup = data_get($combatSummary, 'by_group') ?? [];
    $aggregatedSpawns = data_get($combat, 'aggregated_spawns') ?? [];
    $entitySpawns = data_get($resource, 'entity_spawns') ?? [];

    $totalMin = data_get($combatSummary, 'total.min');
    $totalMax = data_get($combatSummary, 'total.max');

    $roleLabels = [
        'enemy' => 'Enemies',
        'defend_target' => 'Defend Targets',
        'escort_target' => 'Escort Targets',
        'other' => 'Other',
    ];
    $roleBadge = [
        'enemy' => 'badge-error',
        'defend_target' => 'badge-warning',
        'escort_target' => 'badge-info',
        'other' => 'badge-soft',
    ];

    $hasDefendObjective = data_get($resource, 'has_defend_objective');
@endphp

<section {{ $attributes->merge(['class' => 'space-y-4', 'data-testid' => 'mission-combat-section']) }}>
    <div class="flex items-center gap-3 flex-wrap">
        <h2 class="text-lg font-semibold tracking-tight">Combat</h2>
        @if ($hasDefendObjective)
            <span class="badge badge-warning badge-sm">Defend Objective</span>
        @endif
    </div>

    <div class="card card-border bg-base-100 shadow">
        <div class="card-body p-5 sm:p-6 space-y-6">
            @if ($aggregatedSpawns !== [])
                <div>
                    <h3 class="font-semibold uppercase text-subtle mb-3">Spawns</h3>
                    <div class="overflow-x-auto">
                        <table class="table table-sm table-zebra">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Group</th>
                                    <th>Kind</th>
                                    <th>Concurrent</th>
                                    <th>Ships</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($aggregatedSpawns as $spawn)
                                    <tr>
                                        <td><span class="badge {{ $roleBadge[$spawn['role']] }} badge-sm">{{ $roleLabels[$spawn['role']] }}</span></td>
                                        <td>
                                            @php
                                                $spawnGroup = $spawn['group_name'] ?? '-';
                                                $isLong = Str::length($spawnGroup) > 35;
                                            @endphp
                                            @if ($isLong)
                                                <span title="{{ $spawnGroup }}" class="inline-block max-w-48 truncate">{{ $spawnGroup }}</span>
                                            @else
                                                {{ $spawnGroup }}
                                            @endif
                                            @if ($spawn['weight'] !== null)
                                                <span class="badge badge-soft badge-xs ml-1">&times;{{ $spawn['weight'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $spawnKind = $spawn['spawn_kind'];
                                            @endphp
                                            @if ($spawnKind === 'Npc')
                                                <span class="badge badge-info badge-sm">NPC</span>
                                            @elseif ($spawnKind === 'Ship')
                                                <span class="badge badge-warning badge-sm">Ship</span>
                                            @else
                                                {{ $spawnKind ?? '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($spawn['concurrent_min'] !== null || $spawn['concurrent_max'] !== null)
                                                {{ Format::range($spawn['concurrent_min'], $spawn['concurrent_max'], '', 0) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @foreach ($spawn['ships'] ?? [] as $i => $ship)
                                                @if ($i > 0), @endif
                                                @if (! empty($ship['class_name']))
                                                    <a href="{{ route('web.vehicles.show', $ship['class_name']) }}" class="link-primary">{{ $ship['name'] }}</a>
                                                @else
                                                    <span class="text-subtle">{{ $ship['name'] }}</span>
                                                @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($entitySpawns !== [])
                <div>
                    <h3 class="font-semibold uppercase text-subtle mb-3">Entity Spawns</h3>
                    <div class="overflow-x-auto">
                        <table class="table table-sm table-zebra">
                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th>Amount</th>
                                    <th>Tags</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entitySpawns as $entity)
                                    <tr>
                                        <td>{{ data_get($entity, 'group_name') ?? '-' }}</td>
                                        <td>{{ data_get($entity, 'amount') ?? '-' }}</td>
                                        <td>
                                            @php
                                                $allPositiveTags = data_get($entity, 'merged_tags', []);
                                            @endphp
                                            @if ($allPositiveTags !== [])
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($allPositiveTags as $tag)
                                                        <span class="badge badge-soft badge-sm">{{ $tag }}</span>
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
        </div>
    </div>
</section>
