@props([
    'blueprint',
])

@php
    $blueprintUuid = data_get($blueprint, 'uuid');
    $blueprintWebUrl = data_get($blueprint, 'web_url');
    $craftTimeLabel = data_get($blueprint, 'craft_time_label');
    $isAvailableByDefault = data_get($blueprint, 'is_available_by_default');
    $requirementGroups = collect(data_get($blueprint, 'requirement_groups', []))
        ->filter(static fn (mixed $group): bool => is_array($group))
        ->values();
    $unlockingMissionsGrouped = collect(data_get($blueprint, 'unlocking_missions_grouped', []))
        ->filter(static fn (mixed $group): bool => is_array($group))
        ->values();
    $unlockingMissionsCount = (int) data_get($blueprint, 'unlocking_missions_count', 0);
    $maxUnlockingMissions = 5;

    $versionQuery = request()->query('version');
    $blueprintUrl = $blueprintWebUrl;

    if (is_string($blueprintUuid) && $blueprintUuid !== '') {
        $blueprintUrl = route('web.blueprints.show', array_filter([
            'blueprint' => $blueprintUuid,
            'version' => $versionQuery,
        ]));
    }

    $hasIngredients = $requirementGroups->isNotEmpty();
    $hasUnlockingMissions = ! $isAvailableByDefault && $unlockingMissionsGrouped->isNotEmpty();

    $flattenChildren = static function (array $nodes) use (&$flattenChildren): array {
        $result = [];
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }
            $children = data_get($node, 'children', []);
            if (is_array($children) && $children !== [] && data_get($node, 'kind') === 'group') {
                $result = array_merge($result, $flattenChildren($children));
                continue;
            }
            $result[] = $node;
        }

        return $result;
    };

    $resolveLabel = static function (mixed $name, mixed $key, string $fallback = 'Aspect'): string {
        $normalizedName = is_string($name) ? trim($name) : '';
        if ($normalizedName !== '' && ! str_contains(strtoupper($normalizedName), 'PLACEHOLDER')) {
            return $normalizedName;
        }
        $normalizedKey = is_string($key) ? trim($key) : '';
        if ($normalizedKey !== '') {
            return \Illuminate\Support\Str::headline($normalizedKey);
        }

        return $fallback;
    };
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow', 'data-testid' => 'item-blueprint-card']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <h2 class="card-title text-base">
            @if (is_string($blueprintUrl) && $blueprintUrl !== '')
                <a href="{{ $blueprintUrl }}" class="link link-hover link-primary" data-testid="item-blueprint-card-link">Blueprint</a>
            @else
                Blueprint
            @endif
        </h2>

        <x-dl-container>
            <x-slot:head>
                <x-dt-dd label="Craft Time" :value="$craftTimeLabel">{{ $craftTimeLabel }}</x-dt-dd>
                <x-dt-dd label="Availability" :value="$isAvailableByDefault">
                    @if ($isAvailableByDefault === true)
                        <span class="badge badge-success badge-sm badge-outline">Default</span>
                    @else
                        <span class="badge badge-warning badge-sm badge-outline">Unlock required</span>
                    @endif
                </x-dt-dd>
            </x-slot:head>
        </x-dl-container>

        <div class="{{ $hasUnlockingMissions ? 'grid grid-cols-1 gap-6 sm:grid-cols-2' : '' }}">
            @if ($hasIngredients)
                <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <caption class="sr-only">Blueprint ingredients</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Aspect</th>
                                    <th scope="col">Ingredient</th>
                                    <th scope="col" class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requirementGroups as $group)
                                    @php
                                        $groupName = $resolveLabel(data_get($group, 'name'), data_get($group, 'key'));
                                        $groupChildren = $flattenChildren(data_get($group, 'children', []));
                                    @endphp

                                    @foreach ($groupChildren as $child)
                                        @php
                                            $ingredientName = data_get($child, 'name') ?? 'Unknown';
                                            $ingredientQuantityScu = data_get($child, 'quantity_scu');
                                            $ingredientQuantity = data_get($child, 'quantity');
                                            $ingredientUuid = data_get($child, 'uuid');
                                            $ingredientKind = data_get($child, 'kind');

                                            $ingredientWebUrl = null;
                                            if (is_string($ingredientUuid) && $ingredientUuid !== '') {
                                                $ingredientWebUrl = $ingredientKind === 'item'
                                                    ? route('web.items.show', array_filter(['item' => $ingredientUuid, 'version' => $versionQuery]))
                                                    : route('web.commodities.show', array_filter(['identifier' => $ingredientUuid, 'version' => $versionQuery]));
                                            }
                                        @endphp

                                        <tr>
                                            <td class="text-subtle">{{ $groupName }}</td>
                                            <td>
                                                @if (is_string($ingredientWebUrl) && $ingredientWebUrl !== '')
                                                    <a href="{{ $ingredientWebUrl }}" class="link link-hover link-primary">{{ $ingredientName }}</a>
                                                @else
                                                    {{ $ingredientName }}
                                                @endif
                                            </td>
                                            <td class="text-right text-subtle">
                                                @if ($ingredientQuantity !== null && $ingredientQuantity > 0)
                                                    {{ $ingredientQuantity }}×
                                                @elseif ($ingredientQuantityScu !== null && $ingredientQuantityScu > 0)
                                                    {{ $ingredientQuantityScu }} SCU
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                </div>
            @endif

            @if ($hasUnlockingMissions)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Unlocking missions</div>
                    <div class="mt-2 space-y-2">
                        @php
                            $shownCount = 0;
                        @endphp

                        @foreach ($unlockingMissionsGrouped as $group)
                            @foreach (data_get($group, 'missions', []) as $mission)
                                @if ($shownCount >= $maxUnlockingMissions)
                                    @break
                                @endif

                                @php
                                    $shownCount++;
                                    $missionTitle = data_get($mission, 'title', 'Unknown mission');
                                    $missionWebUrl = data_get($mission, 'web_url');
                                    $missionCount = (int) data_get($mission, 'count', 1);
                                    $groupLabel = data_get($group, 'label');
                                @endphp

                                <div class="flex items-center gap-1.5 text-sm">
                                    @if (is_string($missionWebUrl) && $missionWebUrl !== '')
                                        <a href="{{ $missionWebUrl }}" class="link link-hover link-primary">{{ $missionTitle }}</a>
                                    @else
                                        <span>{{ $missionTitle }}</span>
                                    @endif
                                    @if ($missionCount > 1)
                                        <span class="badge badge-soft badge-sm">&times;{{ $missionCount }}</span>
                                    @endif
                                    <span class="text-xs text-subtle">{{ $groupLabel }}</span>
                                </div>
                            @endforeach
                        @endforeach

                        @if ($unlockingMissionsCount > $shownCount && is_string($blueprintUrl) && $blueprintUrl !== '')
                            <a href="{{ $blueprintUrl }}" class="text-sm text-subtle link link-hover">+{{ $unlockingMissionsCount - $shownCount }} more</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
