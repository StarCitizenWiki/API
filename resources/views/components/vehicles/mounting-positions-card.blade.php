@use('App\Support\Format')
@use('Illuminate\Support\Str')
@props(['vehicle'])

@php
    $tabs = [
        'Turret' => 'Turrets',
        'Fixed' => 'Fixed',
        'Gimbal' => 'Gimbals',
        'Rack' => 'Racks',
        'Missile' => 'Missiles',
        'Tool' => 'Tools',
        'Device' => 'Devices',
        'Vacant' => 'Vacant',
        'Unclassified' => 'Unclassified',
    ];
    $roles = ['Manned' => 'Manned', 'Remote' => 'Remote', 'Pdc' => 'PDC', 'Autonomous' => 'Autonomous', 'Unknown' => 'Unknown role'];
    $positions = collect($vehicle['mounting_positions']);
    $groups = $positions->groupBy(static function (array $position) use ($tabs): string {
        if (($position['occupied'] ?? null) === false) {
            return 'Vacant';
        }

        $type = $position['mount_type'] ?? '';

        return isset($tabs[$type]) && $type !== 'Vacant' ? $type : 'Unclassified';
    });
    $tabs = array_intersect_key($tabs, $groups->all());
    $turretsByRole = collect($groups->get('Turret', []))->groupBy(static function (array $position) use ($roles): string {
        $role = $position['turret_role'] ?? '';

        return isset($roles[$role]) ? $role : 'Unknown';
    });

    $portsById = [];
    $pending = $vehicle['ports'] ?? [];
    while ($pending !== []) {
        $port = array_pop($pending);
        if (isset($port['port_id'])) {
            $portsById[$port['port_id']] = $port;
        }
        array_push($pending, ...($port['ports'] ?? []));
    }
    $portLabel = static function (array $port, ?string $hardpoint = null): ?string {
        foreach (['port_display_name', 'part_display_name'] as $field) {
            $label = $port[$field] ?? '';
            if ($label !== '' && ! Str::startsWith($label, ['port_', '@port_'])) {
                return $label;
            }
        }

        $name = $port['name'] ?? $hardpoint;

        return filled($name) ? Str::headline(Str::chopStart($name, 'hardpoint_')) : null;
    };
    $statLabels = ['dps' => 'DPS', 'sustained_dps' => 'Sustained DPS', 'alpha' => 'Alpha'];
@endphp

<section class="card card-border bg-base-100 shadow" aria-labelledby="mounting-positions-heading">
    <div class="card-body p-5 sm:p-6">
        <h2 id="mounting-positions-heading" class="card-title text-base">Mounting Positions</h2>
        @if ($positions->isEmpty())
            <p class="text-sm text-subtle">No mounting positions</p>
        @else
            <div role="tablist" aria-labelledby="mounting-positions-heading" class="tabs tabs-border">
                @foreach ($tabs as $type => $title)
                    <input type="radio" name="mounting-position-tab" role="tab" class="tab"
                           id="mounting-tab-{{ $type }}" aria-controls="mounting-panel-{{ $type }}"
                           aria-label="{{ $title }}" @checked($loop->first) />
                    <div role="tabpanel" id="mounting-panel-{{ $type }}" aria-labelledby="mounting-tab-{{ $type }}" class="tab-content pt-4">
                        <div class="max-h-96 overflow-auto">
                            <table class="table table-sm">
                                <thead class="sticky top-0 z-10 bg-base-100">
                                    <tr>
                                        <th scope="col">Position</th>
                                        <th scope="col">Installed size</th>
                                        <th scope="col">Port capacity</th>
                                        <th scope="col">Operated from</th>
                                        <th scope="col">Combat stats</th>
                                    </tr>
                                </thead>
                                @foreach ($type === 'Turret' ? $roles : ['' => null] as $role => $heading)
                                    @php
                                        $rows = $heading === null ? $groups[$type] : $turretsByRole->get($role, []);
                                    @endphp
                                    @continue(count($rows) === 0)
                                    <tbody>
                                        @if ($heading !== null)
                                            <tr class="bg-base-200/50">
                                                <th colspan="5" scope="rowgroup" class="text-xs uppercase tracking-wide">{{ $heading }}</th>
                                            </tr>
                                        @endif
                                        @foreach ($rows as $position)
                                            @php
                                                $port = $portsById[$position['port_id'] ?? ''] ?? [];
                                                $assembly = data_get($port, 'equipped_item.name') ?? $port['class_name'] ?? null;
                                                $assemblyUrl = data_get($port, 'equipped_item.web_url');
                                                $vacant = ($position['occupied'] ?? null) === false;
                                                $installedSize = $position['installed_size'] ?? $port['installed_size'] ?? null;
                                                $min = $position['min_size'] ?? null;
                                                $max = $position['max_size'] ?? null;
                                                $capacity = match (true) {
                                                    $min !== null && $max !== null && $min !== $max => 'S'.$min.'-S'.$max,
                                                    $min !== null && $max === null => 'Min S'.$min,
                                                    $min === null && $max !== null => 'Max S'.$max,
                                                    $min !== null => 'S'.$min,
                                                    default => '-',
                                                };
                                                $totals = $position['combat_stats'] ?? [];
                                            @endphp
                                            <tr class="align-top">
                                                <th scope="row" class="min-w-44 font-medium">
                                                    <div>{{ $portLabel($port, $position['hardpoint_name'] ?? null) ?? 'Unnamed position' }}</div>
                                                    @if (! $vacant && $assembly !== null)
                                                        <div class="text-xs font-normal text-subtle">
                                                            @if ($assemblyUrl !== null)
                                                                <a href="{{ $assemblyUrl }}" class="link link-hover link-primary">{{ $assembly }}</a>
                                                            @else
                                                                {{ $assembly }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if (! empty($position['payload']))
                                                        <ul class="mt-1 space-y-1 border-s border-base-300 ps-3 text-xs font-normal">
                                                            @foreach ($position['payload'] as $item)
                                                                @php
                                                                    $payloadPort = $portsById[$item['port_id'] ?? ''] ?? [];
                                                                    $label = $portLabel($payloadPort);
                                                                    $itemName = $item['name'] ?? $item['class_name'] ?? 'Unknown item';
                                                                    $itemUrl = data_get($payloadPort, 'equipped_item.web_url');
                                                                @endphp
                                                                <li>
                                                                    @if ($label !== null)<span class="text-subtle">{{ $label }}:</span>@endif
                                                                    @if ($itemUrl !== null)
                                                                        <a href="{{ $itemUrl }}" class="link link-hover link-primary">{{ $itemName }}</a>
                                                                    @else
                                                                        {{ $itemName }}
                                                                    @endif
                                                                    @if (isset($item['size']))<span class="text-subtle">(S{{ $item['size'] }})</span>@endif
                                                                    @foreach ($statLabels + ['missile_damage' => 'Missile damage'] as $stat => $statLabel)
                                                                        @if (isset($item['combat_stats'][$stat]))
                                                                            <div class="text-subtle">{{ $statLabel }}: {{ Format::numberOrDash($item['combat_stats'][$stat], 1) }}</div>
                                                                        @endif
                                                                    @endforeach
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </th>
                                                <td class="whitespace-nowrap">{{ ! $vacant && $installedSize !== null ? 'S'.$installedSize : '-' }}</td>
                                                <td class="whitespace-nowrap">{{ $capacity }}</td>
                                                <td class="min-w-32">
                                                    @forelse ($vacant ? [] : ($position['operating_stations'] ?? []) as $station)
                                                        <div class="mb-1 last:mb-0">{{ $station['name'] }}</div>
                                                    @empty
                                                        <span class="text-subtle">{{ $vacant ? '-' : 'Unknown' }}</span>
                                                    @endforelse
                                                </td>
                                                <td class="min-w-28 tabular-nums">
                                                    @foreach ($statLabels as $stat => $label)
                                                        @if (isset($totals[$stat]))
                                                            <div class="whitespace-nowrap">{{ $label }}: {{ Format::numberOrDash($totals[$stat], 1) }}</div>
                                                        @endif
                                                    @endforeach
                                                    @if (isset($totals['missile_damage']))
                                                        <div class="whitespace-nowrap">Missile damage: {{ Format::numberOrDash($totals['missile_damage'], 1) }}</div>
                                                    @endif
                                                    @if ($totals === [])
                                                        <span class="text-subtle">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @endforeach
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
