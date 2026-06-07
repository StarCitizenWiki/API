<?php

declare(strict_types=1);

namespace App\Support\Resources;

use Carbon\CarbonInterval;

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
        if ($areas === null || $areas === []) {
            return null;
        }

        $filtered = array_map(static fn (array $area): ?array => ($area['global_modifier'] ?? null) !== null && $area['global_modifier'] > 1 ? [
            'name' => $area['name'] ?? null,
            'global_modifier' => $area['global_modifier'] ?? null,
        ] : null, $areas)
                |> array_filter(...)
                |> array_values(...);

        return $filtered !== [] ? $filtered : null;
    }

    protected static function formatAreaExceptions(mixed $areas, ?string $groupName, ?string $resourceUuid): ?array
    {
        if ($areas === null || $areas === [] || $resourceUuid === null) {
            return null;
        }

        $exceptions = array_map(function (array $area) use ($groupName, $resourceUuid): ?array {
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
        }, $areas)
                |> array_filter(...)
                |> array_values(...);

        return $exceptions !== [] ? $exceptions : null;
    }

    protected static function formatAreas(mixed $areas, ?string $groupName = null): ?array
    {
        if ($areas === null || $areas === []) {
            return null;
        }

        return array_map(static fn (array $area): array => [
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
        ], $areas);
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

    protected static function extractHarvestableSetup(mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $setup = $data['harvestable_setup'] ?? null;

        if ($setup === null) {
            return null;
        }

        $respawnSeconds = isset($setup['RespawnInSlotTime']) && is_numeric($setup['RespawnInSlotTime'])
            ? (int) $setup['RespawnInSlotTime'] : null;
        $despawnSeconds = isset($setup['DespawnTimeSeconds']) && is_numeric($setup['DespawnTimeSeconds'])
            ? (int) $setup['DespawnTimeSeconds'] : null;
        $waitSeconds = isset($setup['AdditionalWaitForNearbyPlayersSeconds']) && is_numeric($setup['AdditionalWaitForNearbyPlayersSeconds'])
            ? (int) $setup['AdditionalWaitForNearbyPlayersSeconds'] : null;

        return [
            'respawn_seconds' => $respawnSeconds,
            'respawn_formatted' => $respawnSeconds !== null ? self::formatDuration($respawnSeconds) : null,
            'despawn_seconds' => $despawnSeconds,
            'despawn_formatted' => $despawnSeconds !== null ? self::formatDuration($despawnSeconds) : null,
            'relative_probability' => isset($setup['RelativeProbability']) && is_numeric($setup['RelativeProbability'])
                ? (float) $setup['RelativeProbability'] : null,
            'relative_probability_percent' => isset($setup['RelativeProbability']) && is_numeric($setup['RelativeProbability'])
                ? self::formatPercent((float) $setup['RelativeProbability'], 0) : null,
            'respawn_multiplier' => isset($setup['RespawnTimeMultiplier']) && is_numeric($setup['RespawnTimeMultiplier'])
                ? (float) $setup['RespawnTimeMultiplier'] : null,
            'additional_wait_seconds' => $waitSeconds,
            'additional_wait_formatted' => $waitSeconds !== null ? self::formatDuration($waitSeconds) : null,
        ];
    }

    protected static function formatDuration(int $seconds): string
    {
        return CarbonInterval::seconds($seconds)
            ->locale('en')
            ->cascade()
            ->forHumans(short: true);
    }

    protected static function buildDepositBase(array $depositPairs, mixed $resourceData, ?int $commodityId): array
    {
        $representative = $depositPairs[0]['resourceLocation'];

        $depQMin = PHP_INT_MAX;
        $depQMax = PHP_INT_MIN;
        $relProbMin = INF;
        $relProbMax = -INF;
        $providerNames = [];
        foreach ($depositPairs as $pair) {
            $rl = $pair['resourceLocation'];

            if ($rl->quality_min !== null && $rl->quality_min < $depQMin) {
                $depQMin = $rl->quality_min;
            }

            if ($rl->quality_max !== null && $rl->quality_max > $depQMax) {
                $depQMax = $rl->quality_max;
            }

            $rp = (float) $rl->relative_probability;

            if ($rp < $relProbMin) {
                $relProbMin = $rp;
            }

            if ($rp > $relProbMax) {
                $relProbMax = $rp;
            }

            $pn = $rl->provider?->provider_name;

            if ($pn !== null) {
                $providerNames[$pn] = true;
            }
        }

        return [
            'key' => $resourceData->key,
            'resource_uuid' => $resourceData->resource->uuid,
            'label' => self::parseDepositLabel($resourceData->key),
            'signature' => $resourceData->signature,
            'area_exceptions' => self::formatAreaExceptions($representative->provider?->areas, $representative->group_name, $resourceData->resource->uuid),
            'clustering' => self::extractClustering($representative->data),
            'harvestable_setup' => self::extractHarvestableSetup($representative->data),
            'provider_names' => array_keys($providerNames),
            'materials' => self::buildMaterials($depositPairs, $resourceData, $commodityId),
            'quality_min' => $depQMin === PHP_INT_MAX ? null : $depQMin,
            'quality_max' => $depQMax === PHP_INT_MIN ? null : $depQMax,
            'relative_probability_min' => $relProbMin,
            'relative_probability_max' => $relProbMax,
            'relative_probability_min_percent' => self::formatPercent($relProbMin),
            'relative_probability_max_percent' => self::formatPercent($relProbMax),
        ];
    }

    protected static function buildMaterials(array $depositPairs, mixed $resourceData, ?int $primaryCommodityId): array
    {
        $seen = [];
        $materials = [];
        foreach ($depositPairs as $pair) {
            $id = $pair['resourceLocation']->id;
            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;

            $rl = $pair['resourceLocation'];
            $commodity = $rl->commodity;

            $qqValues = data_get($rl->data, 'quality_quantization');

            $materials[] = [
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
                'quality_quantized_values' => $qqValues,
                'quality_quantization' => $qqValues,
            ];
        }

        usort($materials, static fn (array $a, array $b): int => $b['max_percentage'] <=> $a['max_percentage']);

        return $materials;
    }
}
