<?php

declare(strict_types=1);

namespace App\Support\QueryBuilder\Includes;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludeInterface;

/**
 * Allows accepting an include without eager-loading an Eloquent relationship.
 */
class IncludedPassthrough implements IncludeInterface
{
    public function __invoke(Builder $query, string $relation)
    {
        // no-op
    }
}
