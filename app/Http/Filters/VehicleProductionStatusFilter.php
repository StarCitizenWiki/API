<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class VehicleProductionStatusFilter implements Filter
{
    /**
     * Filter vehicles by production status slug via Ship-Matrix join.
     *
     * @param  mixed  $value
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        $statusSlug = strtolower((string) $value);

        $query->whereHas('shipMatrixVehicle.productionStatus', function (Builder $q) use ($statusSlug) {
            $q->where('slug', $statusSlug);
        });
    }
}
