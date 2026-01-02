<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class ShipMatrixFocusFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $focusSlugs = is_array($value) ? $value : explode(',', (string) $value);

        $query->whereHas('foci', function (Builder $q) use ($focusSlugs) {
            $q->whereIn('slug', array_map('strtolower', $focusSlugs));
        });
    }
}
