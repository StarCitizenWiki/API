<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class ItemVariantsFilter implements Filter
{
    /**
     * When filter value is falsy, only return base items (items without a base_id).
     * When filter value is truthy, return all items including variants.
     *
     * @param  mixed  $value
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        if (! $value) {
            $query->whereNull('base_id');
        }
    }
}
