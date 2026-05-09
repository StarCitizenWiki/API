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

    $formatRelayName = static function (string $hardpoint): string {
        $name = Str::after($hardpoint, 'hardpoint_relay_');
        $name = str_replace('_', ' ', $name);

        return Str::title($name);
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

        <div @if(count($relays) > 1) class="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-2" @endif>
            @foreach($relays as $index => $relay)
                @php
                    $relayName = $formatRelayName(data_get($relay, 'hardpoint', ''));
                    $fuseSlots = data_get($relay, 'fuse_slots');
                    $connectionCount = data_get($relay, 'connection_count');
                    $room = data_get($relay, 'room');
                    $connectedGroups = data_get($relay, 'connected_hardpoints', []);
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
                        @if (is_array($connectedGroups) && $connectedGroups !== [])
                            <div class="flex flex-col gap-3">
                                @foreach ($connectedGroups as $group)
                                    @php
                                        $category = data_get($group, 'category', 'Other');
                                        $count = data_get($group, 'count', 0);
                                        $items = data_get($group, 'items', []);
                                    @endphp

                                    <div>
                                        <span class="text-sm font-semibold uppercase tracking-wide text-subtle">
                                            {{ $category }}
                                            <span class="font-normal">({{ $count }})</span>
                                        </span>

                                        @if (is_array($items) && $items !== [])
                                            <div class="pl-4 border-l border-base-300 mt-1 space-y-0.5">
                                                @foreach ($items as $item)
                                                    @php
                                                        $itemName = data_get($item, 'item_name');
                                                        $hardpoint = data_get($item, 'hardpoint');
                                                    @endphp

                                                    <div
                                                        class="text-sm"
                                                        @if ($itemName && $hardpoint && ! Str::startsWith($itemName, '<='))
                                                            title="{{ $hardpoint }}"
                                                        @endif
                                                    >
                                                        <span class="font-medium">
                                                            @if ($itemName && ! Str::startsWith($itemName, '<='))
                                                                {{ $itemName }}
                                                            @elseif ($hardpoint)
                                                                <span class="font-normal text-subtle">{{ $hardpoint }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
