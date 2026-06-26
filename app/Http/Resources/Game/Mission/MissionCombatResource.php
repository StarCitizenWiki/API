<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission_combat',
    title: 'Mission Combat',
    properties: [
        new OA\Property(
            property: 'summary',
            properties: [
                new OA\Property(
                    property: 'total',
                    properties: [
                        new OA\Property(property: 'min', type: 'integer'),
                        new OA\Property(property: 'max', type: 'integer'),
                    ],
                    type: 'object'
                ),
                new OA\Property(
                    property: 'by_group',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'group_name', type: 'string'),
                            new OA\Property(property: 'min', type: 'integer'),
                            new OA\Property(property: 'max', type: 'integer'),
                        ],
                        type: 'object'
                    )
                ),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'spawns',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'role', type: 'string', nullable: true),
                    new OA\Property(property: 'weight', type: 'integer', nullable: true),
                    new OA\Property(property: 'group_name', type: 'string', nullable: true),
                    new OA\Property(property: 'spawn_kind', type: 'string', nullable: true),
                    new OA\Property(property: 'concurrent_amount', type: 'integer', nullable: true),
                    new OA\Property(
                        property: 'ships',
                        description: 'ship models resolved for this spawn pool.',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'class_name', description: 'Base-hull class name. Null for hulls with no player equivalent.', type: 'string', example: 'AEGS_Avenger_Stalker', nullable: true),
                                new OA\Property(property: 'name', description: 'In-game display name.', type: 'string', example: 'Aegis Avenger Stalker', nullable: true),
                            ],
                            type: 'object'
                        )
                    ),
                ],
                type: 'object'
            )
        ),
        new OA\Property(
            property: 'aggregated_spawns',
            description: 'Spawns grouped by role, group_name, and spawn_kind with aggregated concurrent ranges',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'role', type: 'string'),
                    new OA\Property(property: 'group_name', type: 'string', nullable: true),
                    new OA\Property(property: 'spawn_kind', type: 'string', nullable: true),
                    new OA\Property(property: 'concurrent_min', type: 'integer', nullable: true),
                    new OA\Property(property: 'concurrent_max', type: 'integer', nullable: true),
                    new OA\Property(property: 'weight', type: 'integer', nullable: true),
                    new OA\Property(
                        property: 'ships',
                        description: 'Deduped union of ship models over the spawn options in this wave.',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'class_name', description: 'Base-hull class name. Null for hulls with no player equivalent.', type: 'string', example: 'AEGS_Avenger_Stalker', nullable: true),
                                new OA\Property(property: 'name', type: 'string', example: 'Aegis Avenger Stalker', nullable: true),
                            ],
                            type: 'object'
                        )
                    ),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
class MissionCombatResource extends AbstractBaseResource
{
    private const ROLE_ORDER = ['enemy', 'defend_target', 'escort_target'];

    private const ROLE_SORT = ['enemy' => 0, 'defend_target' => 1, 'escort_target' => 2, 'other' => 3];

    private static function normalizeRole(?string $role): string
    {
        return in_array($role, self::ROLE_ORDER, true) ? $role : 'other';
    }

    public function toArray(Request $request): ?array
    {
        $data = $this->resource;
        $summary = Arr::get($data, 'CombatSummary');
        $spawns = Arr::get($data, 'Combat');

        $hasSummary = is_array($summary) && isset($summary['Total']);
        $hasSpawns = is_array($spawns) && ! empty($spawns);

        if (! $hasSummary && ! $hasSpawns) {
            return null;
        }

        $result = [];

        if ($hasSummary) {
            $total = $summary['Total'] ?? [];

            $byGroup = [];

            foreach ($summary['ByGroup'] ?? [] as $group) {
                $byGroup[] = [
                    'group_name' => $group['GroupName'] ?? null,
                    'min' => $group['Min'] ?? null,
                    'max' => $group['Max'] ?? null,
                ];
            }

            $result['summary'] = [
                'total' => [
                    'min' => $total['Min'] ?? null,
                    'max' => $total['Max'] ?? null,
                ],
                'by_group' => $byGroup,
            ];
        }

        if ($hasSpawns) {
            $mappedSpawns = [];

            foreach ($spawns as $spawn) {
                $mappedSpawns[] = [
                    'role' => $spawn['Role'] ?? null,
                    'weight' => $spawn['Weight'] ?? null,
                    'group_name' => $spawn['GroupName'] ?? null,
                    'spawn_kind' => $spawn['SpawnKind'] ?? null,
                    'concurrent_amount' => $spawn['ConcurrentAmount'] ?? null,
                    'ships' => self::mapShips($spawn['Ships'] ?? []),
                ];
            }

            $result['spawns'] = $mappedSpawns;
            $result['aggregated_spawns'] = self::computeAggregatedSpawns($mappedSpawns);
        }

        return $result;
    }

    public static function computeAggregatedSpawns(array $spawns): array
    {
        $groups = [];
        foreach ($spawns as $s) {
            $key = self::normalizeRole($s['role']).'|'.($s['group_name'] ?? '-').'|'.($s['spawn_kind'] ?? '-');
            $groups[$key][] = $s;
        }

        $result = [];
        foreach ($groups as $group) {
            $first = $group[0];
            $concurrent = [];
            $weights = [];

            foreach ($group as $s) {
                if (($s['concurrent_amount'] ?? null) !== null) {
                    $concurrent[] = $s['concurrent_amount'];
                }

                if (($s['weight'] ?? null) !== null && $s['weight'] > 0) {
                    $weights[] = $s['weight'];
                }
            }

            $result[] = [
                'role' => self::normalizeRole($first['role']),
                'group_name' => $first['group_name'],
                'spawn_kind' => $first['spawn_kind'],
                'concurrent_min' => $concurrent !== [] ? min($concurrent) : null,
                'concurrent_max' => $concurrent !== [] ? max($concurrent) : null,
                'weight' => $weights !== [] ? max($weights) : null,
                'ships' => self::unionShips($group),
            ];
        }

        usort($result, static fn (array $a, array $b): int => (self::ROLE_SORT[$a['role']] ?? 99) <=> (self::ROLE_SORT[$b['role']] ?? 99));

        return $result;
    }

    /**
     * @param  array<int, array<string, string|null>>  $ships
     * @return list<array{class_name: ?string, name: ?string}>
     */
    private static function mapShips(array $ships): array
    {
        $mapped = [];

        foreach ($ships as $ship) {
            $mapped[] = [
                'class_name' => $ship['ClassName'] ?? null,
                'name' => $ship['Name'] ?? null,
            ];
        }

        return $mapped;
    }

    /**
     * Deduped union of ships across a wave's spawn options.
     *
     * @param  array<int, array>  $group  mapped spawn rows
     * @return list<array{class_name: ?string, name: ?string}>
     */
    private static function unionShips(array $group): array
    {
        $deduped = [];

        foreach ($group as $spawn) {
            foreach ($spawn['ships'] ?? [] as $ship) {
                $key = $ship['class_name'] ?? $ship['name'];
                $deduped[$key] = $ship;
            }
        }

        usort($deduped, static fn (array $a, array $b): int => ($a['name'] ?? '') <=> ($b['name'] ?? ''));

        return $deduped;
    }
}
