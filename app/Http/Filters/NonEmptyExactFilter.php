<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Exact-match filter that ignores null and empty-string values.
 */
class NonEmptyExactFilter implements Filter
{
    /**
     * @param  mixed  $value
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        $values = is_array($value) ? $value : [$value];

        $values = array_values(array_filter(
            $values,
            static fn (mixed $item): bool => $item !== null && $item !== '',
        ));

        if ($values === []) {
            return;
        }

        $query->whereIn($query->qualifyColumn($property), $values);
    }
}
