<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class ItemVariantsFilter implements Filter
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Builder $query, $value, string $property)
    {
        if (! $value) {
            $query->whereNull('base_id');
        }
    }
}
