<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\Sort;

class SortByRelation implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): Builder
    {
        return $query->orderByPowerJoins($property, $descending ? 'desc' : 'asc');
    }
}
