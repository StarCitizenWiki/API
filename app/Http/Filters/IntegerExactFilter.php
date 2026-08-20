<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Exact-match filter for integer columns with optional letter alias map, e.g. item grade A-G to 1-7
 */
final class IntegerExactFilter implements Filter
{
    /**
     * @param  array<string, int>  $aliases
     */
    public function __construct(private readonly array $aliases = []) {}

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $values = is_array($value) ? $value : [$value];

        $integers = [];

        foreach ($values as $item) {
            if (is_numeric($item)) {
                $integers[] = (int) $item;

                continue;
            }

            $mapped = $this->aliases[strtolower(trim((string) $item))] ?? null;

            if ($mapped !== null) {
                $integers[] = $mapped;
            }
        }

        if ($integers === []) {
            return;
        }

        $query->whereIn($query->qualifyColumn($property), $integers);
    }
}
