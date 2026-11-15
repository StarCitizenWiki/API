<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QueryIdentifier
{
    public static function whereUuidOrSlug(Builder $query, string $identifier): void
    {
        $query->where(Str::isUuid($identifier) ? 'uuid' : 'slug', $identifier);
    }
}
