<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Concerns;

/**
 * Shared normalization helpers for resource and normalizer classes.
 */
trait NormalizesValues
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function arrayNullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function arrayNullableInt(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function nullableNumeric(mixed $value): int|float|null
    {
        if (! is_numeric($value)) {
            return null;
        }

        $numericValue = $value + 0;

        if (is_float($numericValue) && floor($numericValue) === $numericValue) {
            return (int) $numericValue;
        }

        return $numericValue;
    }
}
