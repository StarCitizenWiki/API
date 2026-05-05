<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
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
        return in_array($role, self::ROLE_ORDER, true) ? (string) $role : 'other';
    }

    public function toArray(Request $request): ?array
    {
        $data = $this->resource;
        $summary = $data?->get('CombatSummary');
        $spawns = $data?->get('Combat');

        $hasSummary = is_array($summary) && isset($summary['Total']);
        $hasSpawns = is_array($spawns) && ! empty($spawns);

        if (! $hasSummary && ! $hasSpawns) {
            return null;
        }

        $result = [];

        if ($hasSummary) {
            $total = $summary['Total'] ?? [];

            $byGroup = collect($summary['ByGroup'] ?? [])->map(fn (array $group): array => [
                'group_name' => $group['GroupName'] ?? null,
                'min' => $group['Min'] ?? null,
                'max' => $group['Max'] ?? null,
            ])->values()->all();

            $result['summary'] = [
                'total' => [
                    'min' => $total['Min'] ?? null,
                    'max' => $total['Max'] ?? null,
                ],
                'by_group' => $byGroup,
            ];
        }

        if ($hasSpawns) {
            $mappedSpawns = collect($spawns)->map(fn (array $spawn): array => [
                'role' => $spawn['Role'] ?? null,
                'weight' => $spawn['Weight'] ?? null,
                'group_name' => $spawn['GroupName'] ?? null,
                'spawn_kind' => $spawn['SpawnKind'] ?? null,
                'concurrent_amount' => $spawn['ConcurrentAmount'] ?? null,
            ])->values()->all();

            $result['spawns'] = $mappedSpawns;
            $result['aggregated_spawns'] = self::computeAggregatedSpawns($mappedSpawns);
        }

        return $result;
    }

    public static function computeAggregatedSpawns(array $spawns): array
    {
        return collect($spawns)
            ->groupBy(fn (array $s): string => self::normalizeRole($s['role']).'|'.($s['group_name'] ?? '-').'|'.($s['spawn_kind'] ?? '-'))
            ->map(function ($group): array {
                $first = $group->first();
                $concurrent = $group->map(fn (array $s) => $s['concurrent_amount'])->filter();
                $weights = $group->map(fn (array $s) => $s['weight'])->filter(fn (?int $v): bool => $v !== null && $v > 0);

                return [
                    'role' => self::normalizeRole($first['role']),
                    'group_name' => $first['group_name'],
                    'spawn_kind' => $first['spawn_kind'],
                    'concurrent_min' => $concurrent->min(),
                    'concurrent_max' => $concurrent->max(),
                    'weight' => $weights->isNotEmpty() ? $weights->max() : null,
                ];
            })
            ->sortBy(fn (array $item): int => self::ROLE_SORT[$item['role']] ?? 99)
            ->values()
            ->all();
    }
}
