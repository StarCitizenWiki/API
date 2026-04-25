<?php

declare(strict_types=1);

namespace App\Support\Filters;

use Closure;
use Illuminate\Support\Collection;

final class FilterValues
{
    /**
     * @param  Collection<int, object>  $rows
     * @param  (callable(mixed, ?string): (?string))|null  $labelResolver
     * @return array<int, array{value: mixed, label: string, count: int, group?: string}>
     */
    public static function fromRows(
        Collection $rows,
        ?Closure $valueCaster = null,
        ?callable $labelResolver = null,
        ?string $groupColumn = null,
    ): array {
        return $rows->map(function (object $row) use ($valueCaster, $labelResolver, $groupColumn): array {
            $value = $row->value ?? null;

            if ($valueCaster !== null) {
                $value = $valueCaster($value);
            }

            if ($value === '') {
                $value = null;
            }

            $label = $row->label ?? null;

            if ($label === '') {
                $label = null;
            }

            if ($labelResolver !== null) {
                $label = $labelResolver($value, $label);
            }

            if ($label === null) {
                $label = self::defaultLabel($value);
            }

            $result = [
                'value' => $value,
                'label' => $label,
                'count' => (int) ($row->count ?? 0),
            ];

            if ($groupColumn !== null && isset($row->$groupColumn)) {
                $result['group'] = $row->$groupColumn;
            }

            return $result;
        })->values()->all();
    }

    private static function defaultLabel(mixed $value): string
    {
        if ($value === null) {
            return 'Unknown';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }
}
