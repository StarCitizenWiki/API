@use('Illuminate\Support\Str')
@props(['vehicle'])

@php
    $relayNetwork = data_get($vehicle, 'relay_network');
    $relays = data_get($relayNetwork, 'relays', []);
    $totalFuses = data_get($relayNetwork, 'total_fuses');

    if (! is_array($relays) || $relays === []) {
        return;
    }

    $relayCount = count($relays);

    $primaryCategories = [
        'Shields',
        'Power Plants',
        'Quantum Drives',
        'Coolers',
        'Flight Controller',
        'Life Support',
        'Weapons',
        'Manned Turrets',
        'Remote Turrets',
        'PDC Turrets',
        'Turrets',
        'Missile & Bomb Racks',
        'Counter Measures',
    ];

    $formatRelayName = static function (string $hardpoint): string {
        $name = Str::after($hardpoint, 'hardpoint_relay_');
        $name = str_replace('_', ' ', $name);

        return Str::title($name);
    };

    $splitGroups = static function (array $groups) use ($primaryCategories): array {
        $primary = [];
        $secondary = [];

        foreach ($groups as $group) {
            $category = data_get($group, 'category', 'Other');

            if (in_array($category, $primaryCategories, true)) {
                $primary[] = $group;
            } else {
                $secondary[] = $group;
            }
        }

        return [$primary, $secondary];
    };
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">
            Relay Network
            <span class="flex items-center gap-2 text-sm text-subtle font-normal">
                ({{ $totalFuses }} {{ Str::plural('Fuse', $totalFuses) }} -
                {{ $relayCount }} {{ Str::plural('Relay', $relayCount) }})
            </span>
        </h2>

        <div @if($relayCount > 1) class="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-2" @endif>
            @foreach($relays as $relay)
                @php
                    $relayName = $formatRelayName(data_get($relay, 'hardpoint', ''));
                    $fuseSlots = data_get($relay, 'fuse_slots');
                    $connectionCount = data_get($relay, 'connection_count');
                    $connectedGroups = data_get($relay, 'connected_hardpoints', []);
                    [$primaryGroups, $secondaryGroups] = $splitGroups($connectedGroups);
                    $hasSecondary = $secondaryGroups !== [];
                    $secondaryCount = array_sum(array_map(fn (array $g) => data_get($g, 'count', 0), $secondaryGroups));
                    $secondaryLabel = implode(', ', array_map(fn (array $g) => data_get($g, 'category', ''), $secondaryGroups));
                @endphp

                <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                    <summary class="collapse-title min-h-11 py-3">
                        <span class="font-semibold block w-full" title="{{ data_get($relay, 'hardpoint', '') }}">
                            {{ $relayName }}
                        </span>
                        <span class="text-xs text-muted">
                            @if ($fuseSlots)
                                <span>{{ $fuseSlots }} {{ Str::plural('fuse slot', $fuseSlots) }} - </span>
                            @endif
                            <span>{{ $connectionCount }} connections</span>
                        </span>
                    </summary>

                    <div class="collapse-content max-h-96 overflow-y-auto">
                        @if ($primaryGroups !== [] || $secondaryGroups !== [])
                            <div class="flex flex-col gap-3">
                                @foreach ($primaryGroups as $group)
                                    @include('components.vehicles.partials.relay-category-group', [
                                        'category' => data_get($group, 'category', 'Other'),
                                        'count' => data_get($group, 'count', 0),
                                        'items' => data_get($group, 'items', []),
                                    ])
                                @endforeach

                                @if ($hasSecondary)
                                    <details class="collapse collapse-arrow border border-base-200 bg-base-200/30">
                                        <summary class="collapse-title min-h-0 py-2 px-3 text-xs">
                                            <span class="text-subtle">
                                                {{ $secondaryCount }} more in {{ $secondaryLabel }}
                                            </span>
                                        </summary>

                                        <div class="collapse-content">
                                            @foreach ($secondaryGroups as $group)
                                                @include('components.vehicles.partials.relay-category-group', [
                                                    'category' => data_get($group, 'category', 'Other'),
                                                    'count' => data_get($group, 'count', 0),
                                                    'items' => data_get($group, 'items', []),
                                                ])
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            </div>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
