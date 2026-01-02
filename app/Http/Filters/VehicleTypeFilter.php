<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class VehicleTypeFilter implements Filter
{
    /**
     * Filter vehicles by type slug via Ship-Matrix join.
     *
     * @param  mixed  $value
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        $typeSlug = strtolower((string) $value);

        $query->whereHas('shipMatrixVehicle.type', function (Builder $q) use ($typeSlug) {
            $q->where('slug', $typeSlug);
        });
    }
}
