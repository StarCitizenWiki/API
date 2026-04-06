<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait InteractsWithMiningAggregates
{
    /**
     * @param  Collection<int, object>  $rows
     * @return array{quality_ranges: array<int, array{min: int, max: int}>, min_quality: ?int, max_quality: ?int}
     */
    protected function buildQualitySummary(Collection $rows, string $resourceKey): array
    {
        $qualityRanges = [];

        foreach ($rows as $row) {
            $overrides = data_get(json_decode($row->data ?? '{}', true), 'quality_overrides', []);

            if (! is_array($overrides)) {
                continue;
            }

            foreach ($overrides as $override) {
                if (! is_array($override) || ($override['resource_key'] ?? null) !== $resourceKey) {
                    continue;
                }

                $range = $override['quality_range'] ?? null;

                if (! is_array($range) || ! is_numeric($range['min'] ?? null) || ! is_numeric($range['max'] ?? null)) {
                    continue;
                }

                $min = (int) $range['min'];
                $max = (int) $range['max'];
                $qualityRanges[sprintf('%d:%d', $min, $max)] = [
                    'min' => $min,
                    'max' => $max,
                ];
            }
        }

        $qualityRanges = array_values($qualityRanges);

        usort($qualityRanges, static fn (array $left, array $right): int => [$left['min'], $left['max']] <=> [$right['min'], $right['max']]);

        if ($qualityRanges === []) {
            return $this->emptyQualitySummary();
        }

        return [
            'quality_ranges' => $qualityRanges,
            'min_quality' => min(array_column($qualityRanges, 'min')),
            'max_quality' => max(array_column($qualityRanges, 'max')),
        ];
    }

    /**
     * @return array{quality_ranges: array<int, array{min: int, max: int}>, min_quality: ?int, max_quality: ?int}
     */
    protected function emptyQualitySummary(): array
    {
        return [
            'quality_ranges' => [],
            'min_quality' => null,
            'max_quality' => null,
        ];
    }

    protected function aggregateDistinctExpression(string $column): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return sprintf('GROUP_CONCAT(DISTINCT %s)', $column);
        }

        return sprintf('ARRAY_AGG(DISTINCT %s)', $column);
    }

    protected function jsonNumericExpression(string $column, string $key): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return sprintf("CAST(json_extract(%s, '$.%s') AS REAL)", $column, $key);
        }

        return sprintf("(%s->>'%s')::numeric", $column, $key);
    }

    protected function qualityFilterExistsCondition(): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return <<<'SQL'
            EXISTS (
                SELECT 1
                FROM json_each(COALESCE(quality_source.data, '{}'), '$.quality_overrides') AS quality_override
                WHERE json_extract(quality_override.value, '$.resource_key') = ?
                  AND CAST(json_extract(quality_override.value, '$.quality_range.max') AS REAL) >= ?
            )
            SQL;
        }

        return <<<'SQL'
        EXISTS (
            SELECT 1
            FROM jsonb_array_elements(COALESCE(quality_source.data->'quality_overrides', '[]'::jsonb)) AS quality_override
            WHERE quality_override->>'resource_key' = ?
              AND (quality_override->'quality_range'->>'max')::numeric >= ?
        )
        SQL;
    }
}
