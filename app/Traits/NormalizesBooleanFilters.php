<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Provides boolean normalization for mixed filter values.
 *
 * This trait normalizes various boolean representations (bool, int, string)
 * to proper boolean values for consistent handling across requests and controllers.
 */
trait NormalizesBooleanFilters
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    protected function normalizeFilterFields(array $filters, array $fields): array
    {
        foreach ($fields as $field) {
            if (! array_key_exists($field, $filters)) {
                continue;
            }

            $normalized = $this->normalizeBoolean($filters[$field]);

            if ($normalized !== null) {
                $filters[$field] = $normalized;
            }
        }

        return $filters;
    }

    /**
     * Normalize a mixed value to a boolean.
     *
     * Accepts: true/false (bool), 1/0 (int), '1'/'0' (string),
     * 'true'/'false' (string, case-insensitive, trimmed)
     *
     * Returns null for unrecognizable values.
     */
    protected function normalizeBoolean(mixed $value): ?bool
    {
        return match (true) {
            is_bool($value) => $value,
            $value === 1 || $value === '1' => true,
            $value === 0 || $value === '0' => false,
            is_string($value) && strtolower(trim($value)) === 'true' => true,
            is_string($value) && strtolower(trim($value)) === 'false' => false,
            default => null,
        };
    }
}
