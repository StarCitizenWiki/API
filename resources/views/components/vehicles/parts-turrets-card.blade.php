@use('App\Support\Format')
@props([
    'vehicle',
    'section' => 'all',
])

@php
    $mannedTurrets = data_get($vehicle, 'turrets.manned', []);
    $remoteTurrets = data_get($vehicle, 'turrets.remote', []);
    $pdcTurrets = data_get($vehicle, 'turrets.pdc', []);
    $showParts = in_array($section, ['all', 'parts'], true);
    $showTurrets = in_array($section, ['all', 'turrets'], true);
    $hasTurretData = !empty($mannedTurrets) || !empty($remoteTurrets) || !empty($pdcTurrets);
    $turretTraits = static function (array $turret): array {
        return array_values(array_filter([
            data_get($turret, 'gimballed') ? 'Gimballed' : null,
            data_get($turret, 'fixed') ? 'Fixed' : null,
        ]));
    };
    $turretLabel = static function (array $turret): array {
        $title = data_get($turret, 'hardpoint_name')
            ?? data_get($turret, 'display_name')
            ?? data_get($turret, 'class_name')
            ?? '-';

        $subtitle = collect([
            data_get($turret, 'display_name'),
            data_get($turret, 'class_name'),
        ])->first(static fn ($value) => is_string($value) && $value !== '' && $value !== $title);

        return [
            'title' => $title,
            'subtitle' => $subtitle,
        ];
    };
    $turretSections = [
        'Manned' => [
            'items' => $mannedTurrets,
            'empty' => 'No manned turrets.',
        ],
        'Remote' => [
            'items' => $remoteTurrets,
            'empty' => 'No remote turrets.',
        ],
        'PDC' => [
            'items' => $pdcTurrets,
            'empty' => 'No PDC turrets.',
        ],
    ];
@endphp

@php
    $flattenAllParts = static function (array $parts) use (&$flattenAllParts): array {
        $rows = [];

        foreach ($parts as $part) {
            $damageMax = $part['damage_max'] ?? 0;

            if ($damageMax > 0) {
                $rows[] = [
                    'name' => $part['display_name'] ?? $part['name'] ?? '-',
                    'hp' => $damageMax,
                    'destruction_damage' => $part['destruction_damage'] ?? null,
                    'detach_damage' => $part['detach_damage'] ?? null,
                ];
            }

            $children = $part['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $rows = array_merge($rows, $flattenAllParts($children));
            }
        }

        return $rows;
    };

    $allParts = collect($flattenAllParts(data_get($vehicle, 'parts', [])));

    $criticalParts = $allParts
        ->filter(fn (array $p) => $p['destruction_damage'] !== null)
        ->sortByDesc('hp')
        ->values();

    $detachableParts = $allParts
        ->filter(fn (array $p) => $p['destruction_damage'] === null && $p['detach_damage'] !== null)
        ->sortByDesc('hp')
        ->values();

    $structuralParts = $allParts
        ->filter(fn (array $p) => $p['destruction_damage'] === null && $p['detach_damage'] === null)
        ->sortByDesc('hp')
        ->values();

    $hasParts = $allParts->isNotEmpty();
@endphp

@if ($showParts && $hasParts)
    <section class="card card-border bg-base-100 shadow">
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">Structural Integrity</h2>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="table table-auto table-sm">
                    <thead class="sticky top-0 z-10 bg-base-100">
                    <tr>
                        <th>Part</th>
                        <th class="text-right">HP</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ($criticalParts->isNotEmpty())
                        <tr>
                            <td colspan="2" class="!bg-error/10 !py-1">
                                <span class="text-xs font-semibold uppercase tracking-wide text-error">Critical</span>
                                <span class="text-xs text-subtle ml-2">Destroying these parts destroys the ship</span>
                            </td>
                        </tr>
                        @foreach ($criticalParts as $part)
                            <tr class="bg-error/5">
                                <td class="font-semibold">{{ $part['name'] }}</td>
                                <td class="text-right font-bold">
                                    {{ Format::numberOrDash($part['hp'], 0) }}
                                    @if ($part['destruction_damage'] !== $part['hp'])
                                        <span class="text-xs font-normal text-subtle ml-1">Destroy {{ Format::numberOrDash($part['destruction_damage'], 0) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @if ($detachableParts->isNotEmpty())
                        <tr>
                            <td colspan="2" class="!bg-base-200/50 !py-1">
                                <span class="text-xs font-semibold uppercase tracking-wide">Detachable</span>
                                <span class="text-xs text-subtle ml-2">Can be shot off the ship</span>
                            </td>
                        </tr>
                        @foreach ($detachableParts as $part)
                            <tr>
                                <td class="">{{ $part['name'] }}</td>
                                <td class="text-right font-medium">
                                    {{ Format::numberOrDash($part['hp'], 0) }}
                                    @if ($part['detach_damage'] !== $part['hp'])
                                        <span class="text-xs font-normal text-subtle ml-1">Detach {{ Format::numberOrDash($part['detach_damage'], 0) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @if ($structuralParts->isNotEmpty())
                        <tr>
                            <td colspan="2" class="!bg-base-200/50 !py-1">
                                <span class="text-xs font-semibold uppercase tracking-wide">Structure</span>
                                <span class="text-xs text-subtle ml-2">Takes damage but cannot detach or destroy the ship</span>
                            </td>
                        </tr>
                        @foreach ($structuralParts as $part)
                            <tr>
                                <td>{{ $part['name'] }}</td>
                                <td class="text-right font-medium">{{ Format::numberOrDash($part['hp'], 0) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endif

<!-- Turrets -->
@if ($showTurrets && $hasTurretData)
    <section class="card card-border bg-base-100 shadow">
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">Turrets</h2>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="table table-auto table-sm">
                    <thead class="sticky top-0 z-10 bg-base-100">
                    <tr>
                        <th>Turret</th>
                        <th>Mount</th>
                        <th>Size</th>
                        <th class="text-right">DPS</th>
                        <th class="text-right">Sustained DPS</th>
                        <th class="text-right">Alpha</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($turretSections as $heading => $sectionData)
                        @php
                            $turrets = $sectionData['items'];
                            $hasTurretsInSection = is_array($turrets) && $turrets !== [];
                        @endphp
                        @if ($hasTurretsInSection)
                            <tr>
                                <td colspan="6" class="!bg-base-200/50 !py-1">
                                    <span class="text-xs font-semibold uppercase tracking-wide">{{ $heading }}</span>
                                </td>
                            </tr>
                            @foreach ($turrets as $turret)
                                @php
                                    $traits = $turretTraits($turret);
                                    $label = $turretLabel($turret);
                                    $dpsTotal = data_get($turret, 'dps_total');
                                    $sustainedDpsTotal = data_get($turret, 'sustained_dps_total');
                                    $alphaTotal = data_get($turret, 'alpha_total');
                                    $mountCount = data_get($turret, 'mount_count');
                                    $isPilotSlaveable = data_get($turret, 'is_pilot_slaveable');
                                    $weapons = data_get($turret, 'weapons', []);
                                    $weaponSizes = data_get($turret, 'weapon_sizes', []);
                                    $mounts = data_get($turret, 'mounts', []);
                                    $weaponCount = is_array($weapons) ? count($weapons) : 0;
                                    $rowspan = $weaponCount > 0 ? $weaponCount + 1 : 1;
                                @endphp
                                <tr>
                                    <td rowspan="{{ $rowspan }}" class="min-w-48 align-top">
                                        <div class="font-medium">{{ $label['title'] }}</div>
                                        @if (! empty($label['subtitle']))
                                            <div class="text-xs text-subtle">{{ $label['subtitle'] }}</div>
                                        @endif
                                        <div class="mt-1 flex flex-wrap items-center gap-1">
                                            @foreach ($traits as $trait)
                                                <span class="badge badge-sm badge-ghost">{{ $trait }}</span>
                                            @endforeach
                                            @if ($isPilotSlaveable === true)
                                                <span class="badge badge-sm badge-outline">Slaveable</span>
                                            @endif
                                        </div>
                                    </td>
                                    @if ($weaponCount > 0)
                                        @php
                                            $mount = $mounts[0] ?? null;
                                            $weaponSize = $weaponSizes[0] ?? null;
                                        @endphp
                                        <td>
                                            <div>
                                                @if (isset($weapons[0]['web_url']))
                                                    <a href="{{ $weapons[0]['web_url'] }}" class="link link-hover link-primary">{{ $weapons[0]['name'] ?? $weapons[0]['class_name'] ?? '-' }}</a>
                                                @else
                                                    <span class="font-medium">{{ $weapons[0]['name'] ?? $weapons[0]['class_name'] ?? '-' }}</span>
                                                @endif
                                            </div>
                                            @if ($mount && $weaponCount > 1)
                                                <div class="text-xs text-subtle">{{ data_get($mount, 'display_name') }}</div>
                                            @endif
                                        </td>
                                        <td class="font-medium">{{ $weaponSize ? 'S'.$weaponSize : '-' }}</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash(data_get($weapons[0], 'dps'), 1) }}</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash(data_get($weapons[0], 'sustained_dps'), 1) }}</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash(data_get($weapons[0], 'alpha'), 1) }}</td>
                                    @else
                                        <td class="font-medium">{{ isset($turret['size']) ? 'S'.$turret['size'] : '-' }}@if ($mountCount) (x{{ Format::numberOrDash($mountCount) }}) @endif</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash($dpsTotal, 1) }}</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash($sustainedDpsTotal, 1) }}</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash($alphaTotal, 1) }}</td>
                                    @endif
                                </tr>
                                @for ($i = 1; $i < $weaponCount; $i++)
                                    @php
                                        $weapon = $weapons[$i];
                                        $weaponSize = $weaponSizes[$i] ?? null;
                                        $mount = $mounts[$i] ?? null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div>
                                                @if (isset($weapon['web_url']))
                                                    <a href="{{ $weapon['web_url'] }}" class="link link-hover link-primary">{{ $weapon['name'] ?? $weapon['class_name'] ?? '-' }}</a>
                                                @else
                                                    <span class="font-medium">{{ $weapon['name'] ?? $weapon['class_name'] ?? '-' }}</span>
                                                @endif
                                            </div>
                                            @if ($mount && $weaponCount > 1)
                                                <div class="text-xs text-subtle">{{ data_get($mount, 'display_name') }}</div>
                                            @endif
                                        </td>
                                        <td class="font-medium">{{ $weaponSize ? 'S'.$weaponSize : '-' }}</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash(data_get($weapon, 'dps'), 1) }}</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash(data_get($weapon, 'sustained_dps'), 1) }}</td>
                                        <td class="text-right font-semibold">{{ Format::numberOrDash(data_get($weapon, 'alpha'), 1) }}</td>
                                    </tr>
                                @endfor
                                @if ($weaponCount > 0)
                                    <tr class="text-sm font-semibold text-subtle">
                                        <td>Total</td>
                                        <td></td>
                                        <td class="text-right">{{ Format::numberOrDash($dpsTotal, 1) }}</td>
                                        <td class="text-right">{{ Format::numberOrDash($sustainedDpsTotal, 1) }}</td>
                                        <td class="text-right">{{ Format::numberOrDash($alphaTotal, 1) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endif
