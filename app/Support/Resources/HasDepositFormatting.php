<?php

declare(strict_types=1);

namespace App\Support\Resources;

use Illuminate\Support\Collection;

trait HasDepositFormatting
{
    protected static function resolveMiningType(string $groupName): array
    {
        return match (true) {
            $groupName === 'SpaceShip_Mineables' => ['label' => 'Ship Mining', 'sort_order' => 0],
            $groupName === 'GroundVehicle_Mineables' => ['label' => 'Vehicle Mining', 'sort_order' => 1],
            in_array($groupName, ['FPS_Mineables', 'FPS mineables'], true) => ['label' => 'FPS Mining', 'sort_order' => 2],
            in_array($groupName, ['Harvestables', 'Havestables', 'Plants'], true) => ['label' => 'Harvestables', 'sort_order' => 3],
            str_starts_with($groupName, 'Salvage') => ['label' => 'Salvage', 'sort_order' => 4],
            default => ['label' => str_replace('_', ' ', $groupName), 'sort_order' => 99],
        };
    }

    protected static function formatPercent(float $value, int $precision = 2): float
    {
        return round($value * 100, $precision);
    }

    protected static function parseDepositLabel(string $key): string
    {
        if (str_starts_with($key, 'harvestable_base_')) {
            return self::splitCamelCase(substr($key, strlen('harvestable_base_')));
        }

        if (str_starts_with($key, 'MineableRock_')) {
            return self::extractMineableMaterial(substr($key, strlen('MineableRock_')));
        }

        if (preg_match('/^Asteroid([A-Z])TypeMineableRock(?:_(.+))?$/', $key, $m)) {
            return isset($m[2]) ? self::splitCamelCase($m[2]) : self::splitCamelCase("{$m[1]} Type");
        }

        if (preg_match('/^([A-Z][a-zA-Z]+)MineableRock(?:_(.+))?$/', $key, $m)) {
            return isset($m[2]) ? self::splitCamelCase($m[2]) : self::splitCamelCase($m[1]);
        }

        if ($key === 'MineableRock') {
            return 'Mineable Rock';
        }

        if (str_starts_with($key, 'SalvageableDebris_')) {
            return 'Debris ('.self::splitCamelCase(substr($key, strlen('SalvageableDebris_'))).')';
        }

        if (str_starts_with($key, 'SalvageableRepairable_ShipDebris_')) {
            $parts = explode('_', substr($key, strlen('SalvageableRepairable_ShipDebris_')));
            array_shift($parts);

            return 'Ship Debris ('.implode(' ', array_map(self::splitCamelCase(...), $parts)).')';
        }

        if (str_starts_with($key, 'SalvageScrap_')) {
            return self::splitCamelCase(substr($key, strlen('SalvageScrap_')));
        }

        foreach (['Harvestable_', 'Plant_'] as $prefix) {
            if (str_starts_with($key, $prefix)) {
                $stripped = substr($key, strlen($prefix));
                $parts = explode('_', $stripped);
                $material = array_pop($parts);

                if ($parts === []) {
                    return self::splitCamelCase($material);
                }

                $qualifier = implode(' ', array_map(self::splitCamelCase(...), $parts));

                return trim("{$qualifier} ({$material})");
            }
        }

        return self::splitCamelCase(str_replace('_', ' ', $key));
    }

    private static function splitCamelCase(string $value): string
    {
        return trim((string) preg_replace('/([a-z])([A-Z])/', '$1 $2', ucfirst($value)));
    }

    private static function extractMineableMaterial(string $stripped): string
    {
        $noise = ['TEMPLATE', 'large', 'small', 'RCD', 'Pure', 'nobase'];

        $segments = array_values(array_filter(
            explode('_', $stripped),
            static fn (string $s): bool => ! in_array($s, $noise, true) && $s !== '',
        ));

        if ($segments === []) {
            return 'Unknown';
        }

        $categories = [
            'AsteroidCommon', 'AsteroidEpic', 'AsteroidLegendary', 'AsteroidRare', 'AsteroidUncommon',
            'SurfaceCommon', 'SurfaceEpic', 'SurfaceLegendary', 'SurfaceRare', 'SurfaceUncommon',
            'FPS', 'GroundVehicle', 'test',
        ];

        if (count($segments) > 1 && in_array($segments[0], $categories, true)) {
            array_shift($segments);
        }

        return self::splitCamelCase(array_pop($segments));
    }

    protected static function formatAllAreas(mixed $areas): ?array
    {
        if ($areas === null || $areas->isEmpty()) {
            return null;
        }

        $filtered = $areas
            ->filter(static fn ($area): bool => ($area['global_modifier'] ?? null) !== null && $area['global_modifier'] > 1)
            ->map(static fn ($area): array => [
                'name' => $area['name'] ?? null,
                'global_modifier' => $area['global_modifier'] ?? null,
            ])
            ->values()
            ->all();

        return $filtered !== [] ? $filtered : null;
    }

    protected static function formatAreaExceptions(mixed $areas, ?string $groupName, ?string $resourceUuid): ?array
    {
        if ($areas === null || $areas->isEmpty() || $resourceUuid === null) {
            return null;
        }

        $exceptions = $areas->map(function ($area) use ($groupName, $resourceUuid): ?array {
            $matched = collect($area['modifiers'] ?? [])
                ->first(static fn (array $m): bool => $m['resource_uuid'] === $resourceUuid
                    && ($groupName === null || ($m['group_name'] ?? null) === $groupName));

            if ($matched === null || $matched['modifier'] === 1) {
                return null;
            }

            return [
                'name' => $area['name'] ?? null,
                'modifier' => $matched['modifier'],
            ];
        })->filter()->values()->all();

        return $exceptions !== [] ? $exceptions : null;
    }

    protected static function formatAreas(mixed $areas, ?string $groupName = null): ?array
    {
        if ($areas === null || $areas->isEmpty()) {
            return null;
        }

        return $areas->map(static fn ($area): array => [
            'name' => $area['name'] ?? null,
            'global_modifier' => $area['global_modifier'] ?? null,
            'modifiers' => collect($area['modifiers'] ?? [])
                ->filter(static fn (array $m): bool => $m['resource_uuid'] !== null
                    && ($groupName === null || ($m['group_name'] ?? null) === $groupName))
                ->map(static fn (array $m): array => [
                    'modifier' => $m['modifier'],
                    'resource_uuid' => $m['resource_uuid'],
                    'group_name' => $m['group_name'],
                ])
                ->values()
                ->all(),
        ])->all();
    }

    protected static function extractClustering(mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $clustering = $data['clustering'] ?? null;

        if ($clustering === null) {
            return null;
        }

        return [
            'key' => $clustering['Key'] ?? null,
            'min_size' => $clustering['MinSize'] ?? null,
            'max_size' => $clustering['MaxSize'] ?? null,
            'min_proximity' => $clustering['MinProximity'] ?? null,
            'max_proximity' => $clustering['MaxProximity'] ?? null,
            'probability' => $clustering['ProbabilityOfClustering'] ?? null,
            'probability_percent' => isset($clustering['ProbabilityOfClustering']) && is_numeric($clustering['ProbabilityOfClustering'])
                ? self::formatPercent((float) $clustering['ProbabilityOfClustering'], 0)
                : null,
            'params' => collect($clustering['Params'] ?? [])
                ->map(static fn ($param): array => [
                    'min_size' => $param['MinSize'] ?? null,
                    'max_size' => $param['MaxSize'] ?? null,
                    'min_proximity' => $param['MinProximity'] ?? null,
                    'max_proximity' => $param['MaxProximity'] ?? null,
                    'relative_probability' => $param['RelativeProbability'] ?? null,
                ])
                ->all(),
        ];
    }

    protected static function buildDepositBase(Collection $depositPairs, mixed $resourceData, ?int $commodityId): array
    {
        $representative = $depositPairs->first()['resourceLocation'];

        $depQMin = $depositPairs->min(static fn (array $pair) => $pair['resourceLocation']->quality_min);
        $depQMax = $depositPairs->max(static fn (array $pair) => $pair['resourceLocation']->quality_max);

        $relProbMin = $depositPairs->min(static fn (array $pair): float => (float) $pair['resourceLocation']->relative_probability);
        $relProbMax = $depositPairs->max(static fn (array $pair): float => (float) $pair['resourceLocation']->relative_probability);

        return [
            'key' => $resourceData->key,
            'resource_uuid' => $resourceData->resource->uuid,
            'label' => self::parseDepositLabel($resourceData->key),
            'signature' => $resourceData->signature,
            'area_exceptions' => self::formatAreaExceptions($representative->provider?->areas, $representative->group_name, $resourceData->resource->uuid),
            'clustering' => self::extractClustering($representative->data),
            'materials' => self::buildMaterials($depositPairs, $resourceData, $commodityId),
            'quality_min' => $depQMin,
            'quality_max' => $depQMax,
            'relative_probability_min' => $relProbMin,
            'relative_probability_max' => $relProbMax,
            'relative_probability_min_percent' => self::formatPercent($relProbMin),
            'relative_probability_max_percent' => self::formatPercent($relProbMax),
        ];
    }

    protected static function buildMaterials(Collection $depositPairs, mixed $resourceData, ?int $primaryCommodityId): array
    {
        return $depositPairs
            ->unique(static fn (array $pair): int => $pair['resourceLocation']->id)
            ->map(function (array $pair) use ($primaryCommodityId): array {
                $rl = $pair['resourceLocation'];
                $commodity = $rl->commodity;

                return [
                    'key' => $rl->commodity?->key,
                    'name' => $commodity?->name,
                    'uuid' => $commodity?->uuid,
                    'is_current' => $commodity?->id === $primaryCommodityId,
                    'quality_min' => $rl->quality_min,
                    'quality_max' => $rl->quality_max,
                    'quality_mean' => $rl->quality_mean,
                    'quality_stddev' => $rl->quality_stddev,
                    'min_percentage' => (float) $rl->min_percentage,
                    'max_percentage' => (float) $rl->max_percentage,
                    'instability' => $commodity !== null ? (float) $commodity->instability : null,
                    'resistance' => $commodity !== null ? (float) $commodity->resistance : null,
                    'group_probability' => (float) $rl->group_probability,
                    'group_probability_percent' => self::formatPercent((float) $rl->group_probability),
                    'relative_probability' => (float) $rl->relative_probability,
                    'relative_probability_percent' => self::formatPercent((float) $rl->relative_probability),
                ];
            })
            ->sortByDesc(static fn (array $item): float => $item['max_percentage'])
            ->values()
            ->all();
    }
}
