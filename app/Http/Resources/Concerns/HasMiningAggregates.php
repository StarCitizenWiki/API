<?php

declare(strict_types=1);

namespace App\Http\Resources\Concerns;

use Illuminate\Http\Request;

/**
 * Trait for normalizing mining-related aggregate data in API resources.
 *
 * This trait depends on the base class providing:
 * - arrayString(array $data, string $key): string
 * - arrayNullableString(array $data, string $key): ?string
 * - arrayNullableInt(array $data, string $key): ?int
 * - arrayNullableFloat(array $data, string $key): ?float
 * - urlWithVersion(string $url, Request $request): string
 */
trait HasMiningAggregates
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function miningAggregateFields(array $data, Request $request, string $link, string $webUrl): array
    {
        return [
            'deposit_count' => (int) ($data['deposit_count'] ?? 0),
            'deposit_kinds' => $this->normalizeStringList($data['deposit_kinds'] ?? []),
            'groups' => $this->normalizeStringList($data['groups'] ?? []),
            'min_percentage' => $this->arrayNullableFloat($data, 'min_percentage'),
            'max_percentage' => $this->arrayNullableFloat($data, 'max_percentage'),
            'quality_ranges' => $this->normalizeQualityRanges($data['quality_ranges'] ?? []),
            'min_quality' => $this->arrayNullableInt($data, 'min_quality'),
            'max_quality' => $this->arrayNullableInt($data, 'max_quality'),
            'link' => $link,
            'web_url' => $webUrl,
            'deposits' => $this->when(
                isset($data['deposits']),
                fn (): array => $this->normalizeDeposits($data['deposits'])
            ),
        ];
    }

    /**
     * @return array<int, array{min: int, max: int}>
     */
    protected function normalizeQualityRanges(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $ranges = [];

        foreach ($value as $range) {
            if (! is_array($range)) {
                continue;
            }

            $min = $range['min'] ?? null;
            $max = $range['max'] ?? null;

            if (! is_numeric($min) || ! is_numeric($max)) {
                continue;
            }

            $ranges[] = [
                'min' => (int) $min,
                'max' => (int) $max,
            ];
        }

        return $ranges;
    }

    /**
     * @return array<int, array{uuid: string, name: string, kind: string, group: ?string, min_percentage: ?float, max_percentage: ?float, quality_overrides: array<int, mixed>}>
     */
    protected function normalizeDeposits(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $deposits = [];

        foreach ($value as $deposit) {
            if (! is_array($deposit)) {
                continue;
            }

            $qualityOverrides = $deposit['quality_overrides'] ?? [];

            $deposits[] = [
                'uuid' => $this->arrayString($deposit, 'uuid'),
                'name' => $this->arrayString($deposit, 'name'),
                'kind' => $this->arrayString($deposit, 'kind'),
                'group' => $this->arrayNullableString($deposit, 'group'),
                'min_percentage' => $this->arrayNullableFloat($deposit, 'min_percentage'),
                'max_percentage' => $this->arrayNullableFloat($deposit, 'max_percentage'),
                'quality_overrides' => is_array($qualityOverrides) ? array_values($qualityOverrides) : [],
            ];
        }

        return $deposits;
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeStringList(mixed $value): array
    {
        if (is_array($value)) {
            return $this->filterStringList($value);
        }

        if (! is_string($value)) {
            return [];
        }

        $value = trim($value);

        if ($value === '') {
            return [];
        }

        if (str_starts_with($value, '{') && str_ends_with($value, '}')) {
            $value = substr($value, 1, -1);
        }

        if ($value === '') {
            return [];
        }

        return $this->filterStringList(str_getcsv($value, ',', '"', ''));
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, string>
     */
    private function filterStringList(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $string = trim((string) $value);

            if ($string === '') {
                continue;
            }

            $normalized[] = $string;
        }

        return array_values($normalized);
    }
}
