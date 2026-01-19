<?php

declare(strict_types=1);

namespace App\Support\Filters;

use Closure;
use Illuminate\Support\Collection;

final class FilterValues
{
    /**
     * @param  Collection<int, object>  $rows
     * @return array<int, array{value: mixed, label: string, count: int}>
     */
    public static function fromRows(
        Collection $rows,
        ?Closure $valueCaster = null,
        ?Closure $labelResolver = null
    ): array {
        return $rows->map(function (object $row) use ($valueCaster, $labelResolver): array {
            $value = $row->value ?? null;

            if ($valueCaster !== null) {
                $value = $valueCaster($value);
            }

            if ($value === '') {
                $value = null;
            }

            $label = $row->label ?? null;

            if (is_string($label) && $label === '') {
                $label = null;
            }

            if ($labelResolver !== null) {
                $label = $labelResolver($value, $label);
            }

            if ($label === null) {
                $label = self::defaultLabel($value);
            }

            return [
                'value' => $value,
                'label' => $label,
                'count' => (int) ($row->count ?? 0),
            ];
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
