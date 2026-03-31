@props([
    'vehicle',
    'section' => 'all',
])

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
                    'has_children' => is_array($part['children'] ?? null) && ($part['children'] ?? []) !== [],
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
    $showParts = in_array($section, ['all', 'parts'], true);
    $showTurrets = in_array($section, ['all', 'turrets'], true);
    $turretTraits = static function (array $turret): array {
        return array_values(array_filter([
            data_get($turret, 'gimballed') ? 'Gimballed' : null,
            data_get($turret, 'fixed') ? 'Fixed' : null,
        ]));
    };
@endphp

<!-- Parts -->
@if ($showParts && $flatParts !== [])
    <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
        <summary class="collapse-title min-h-11 py-3 font-semibold">Parts</summary>
        <div class="collapse-content">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-base-200 pb-2 text-xs font-semibold uppercase tracking-wide text-base-content/50">
                    <span>Structure</span>
                    <span>Damage Max</span>
                </div>

                <div class="divide-y divide-base-200">
                    @foreach ($flatParts as $partEntry)
                        @php
                            $part = $partEntry['part'];
                            $depth = $partEntry['depth'];
                            $hasChildren = $partEntry['has_children'];
                            $partName = $part['display_name'] ?? $part['name'] ?? '-';
                        @endphp
                        <div class="flex items-start justify-between gap-4 py-3">
                            <div class="min-w-0" style="padding-left: {{ $depth * 1.25 }}rem;">
                                <div class="flex items-start gap-2">
                                    @if ($depth > 0)
                                        <span class="mt-2 h-px w-3 shrink-0 bg-base-300"></span>
                                    @endif

                                    <div class="min-w-0">
                                        <div @class([
                                            'text-sm leading-5',
                                            'font-semibold text-base-content' => $depth === 0,
                                            'font-medium text-base-content/90' => $depth > 0 && $hasChildren,
                                            'text-base-content/80' => $depth > 0 && ! $hasChildren,
                                        ])>
                                            {{ $partName }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <div class="text-sm font-medium text-base-content">
                                    {{ fmt_or_dash($part['damage_max'], 0) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </details>
@endif

<!-- Turrets -->
@if ($showTurrets && (!empty($mannedTurrets) || !empty($remoteTurrets)))
    <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
        <summary class="collapse-title min-h-11 py-3 font-semibold">Turrets</summary>
        <div class="collapse-content">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-semibold mb-2">Manned</h4>
                    @if (is_array($mannedTurrets) && $mannedTurrets !== [])
                        <div class="overflow-x-auto">
                            <table class="table table-auto table-sm">
                                <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Traits</th>
                                    <th class="hidden sm:table-cell">Weapon Sizes</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($mannedTurrets as $turret)
                                    @php
                                        $traits = $turretTraits($turret);
                                        $weaponSizes = data_get($turret, 'weapon_sizes', []);
                                    @endphp
                                    <tr>
                                        <td class="font-medium">{{ isset($turret['size']) ? 'S'.$turret['size'] : '-' }}</td>
                                        <td>
                                            @if ($traits !== [])
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($traits as $trait)
                                                        <span class="badge badge-ghost badge-sm">{{ $trait }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-base-content/60">-</span>
                                            @endif
                                        </td>
                                        <td class="hidden sm:table-cell">
                                            @if (is_array($weaponSizes) && $weaponSizes !== [])
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($weaponSizes as $weaponSize)
                                                        <span class="badge badge-outline badge-sm">S{{ $weaponSize }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-base-content/60">-</span>
                                            @endif
                                        </td>
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
                            <table class="table table-auto table-sm">
                                <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Traits</th>
                                    <th class="hidden sm:table-cell">Weapon Sizes</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($remoteTurrets as $turret)
                                    @php
                                        $traits = $turretTraits($turret);
                                        $weaponSizes = data_get($turret, 'weapon_sizes', []);
                                    @endphp
                                    <tr>
                                        <td class="font-medium">{{ isset($turret['size']) ? 'S'.$turret['size'] : '-' }}</td>
                                        <td>
                                            @if ($traits !== [])
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($traits as $trait)
                                                        <span class="badge badge-ghost badge-sm">{{ $trait }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-base-content/60">-</span>
                                            @endif
                                        </td>
                                        <td class="hidden sm:table-cell">
                                            @if (is_array($weaponSizes) && $weaponSizes !== [])
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($weaponSizes as $weaponSize)
                                                        <span class="badge badge-outline badge-sm">S{{ $weaponSize }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-base-content/60">-</span>
                                            @endif
                                        </td>
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
