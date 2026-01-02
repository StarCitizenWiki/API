<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class ShipMatrixTypeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $query->whereHas('type', function (Builder $q) use ($value) {
            $q->where('slug', strtolower((string) $value));
        });
    }
}
