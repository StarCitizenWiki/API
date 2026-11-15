<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait ResolvesUuidOrSlug
{
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($field !== null) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        return $query->when(
            Str::isUuid($value),
            fn (Builder $q) => $q->where('uuid', $value),
            fn (Builder $q) => $q->where('slug', $value),
        );
    }
}
