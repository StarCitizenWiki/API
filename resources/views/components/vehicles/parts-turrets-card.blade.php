@props(['vehicle'])

@php
    $parts = data_get($vehicle, 'parts', []);

    $flatParts = [];
    if (is_array($parts) && $parts !== []) {
        $flattenParts = static function (array $parts, int $depth = 0) use (&$flattenParts): array {
            $rows = [];

            foreach ($parts as $part) {
                $rows[] = [
                    'part' => $part,
                    'depth' => $depth,
                ];

                $children = $part['children'] ?? [];
                if (is_array($children) && $children !== []) {
                    $rows = array_merge($rows, $flattenParts($children, $depth + 1));
                }
            }

            return $rows;
        };

        $flatParts = $flattenParts($parts);
    }

    $mannedTurrets = data_get($vehicle, 'turrets.manned', []);
    $remoteTurrets = data_get($vehicle, 'turrets.remote', []);
@endphp

<!-- Parts -->
@if ($flatParts !== [])
    <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
        <summary class="collapse-title min-h-11 py-3 font-semibold">Parts</summary>
        <div class="collapse-content">
            <div class="overflow-x-auto">
                <table class="table table-xs table-zebra">
                    <thead>
                    <tr>
                        <th>Part</th>
                        <th>Damage Max</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($flatParts as $partEntry)
                        @php
                            $part = $partEntry['part'];
                            $depth = $partEntry['depth'];
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap">
                                <span class="block" style="padding-left: {{ $depth * 1.25 }}rem;">
                                    {{ $part['display_name'] ?? $part['name'] ?? '-' }}
                                </span>
                            </td>
                            <td>{{ fmt_or_dash($part['damage_max'], 0) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>
@endif

<!-- Turrets -->
@if (!empty($mannedTurrets) || !empty($remoteTurrets))
    <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
        <summary class="collapse-title min-h-11 py-3 font-semibold">Turrets</summary>
        <div class="collapse-content">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-semibold mb-2">Manned</h4>
                    @if (is_array($mannedTurrets) && $mannedTurrets !== [])
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Fixed</th>
                                    <th>Weapon Sizes</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($mannedTurrets as $turret)
                                    <tr>
                                        <td>{{ $turret['size'] ?? '-' }}</td>
                                        <td>{{ array_key_exists('fixed', $turret) ? ($turret['fixed'] ? 'Yes' : 'No') : '-' }}</td>
                                        <td>{{ ! empty($turret['weapon_sizes']) ? implode(', ', $turret['weapon_sizes']) : '-' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No manned turrets.</div>
                    @endif
                </div>
                <div>
                    <h4 class="text-sm font-semibold mb-2">Remote</h4>
                    @if (is_array($remoteTurrets) && $remoteTurrets !== [])
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Fixed</th>
                                    <th>Weapon Sizes</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($remoteTurrets as $turret)
                                    <tr>
                                        <td>{{ $turret['size'] ?? '-' }}</td>
                                        <td>{{ array_key_exists('fixed', $turret) ? ($turret['fixed'] ? 'Yes' : 'No') : '-' }}</td>
                                        <td>{{ ! empty($turret['weapon_sizes']) ? implode(', ', $turret['weapon_sizes']) : '-' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-sm text-base-content/70">No remote turrets.</div>
                    @endif
                </div>
            </div>
        </div>
    </details>
@endif
