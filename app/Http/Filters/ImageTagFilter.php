<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class ImageTagFilter implements Filter
{
    /**
     * @inheritDoc
     */
    public function __invoke(Builder $query, $value, string $property)
    {
        $query->whereRelation('tags', 'name', $value)
            ->orWhereRelation('tags', 'name_en', $value);
    }
}
