<?php

declare(strict_types=1);

namespace App\Http\Includes;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludeInterface;

class CustomEagerLoadInclude implements IncludeInterface
{
    /**
     * @param  array<int|string, mixed>  $relations
     */
    public function __construct(
        protected array $relations = [],
    ) {}

    public function __invoke(Builder $query, string $include): void
    {
        $query->with($this->relations);
    }
}
