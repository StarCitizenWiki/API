<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class QueryLike
{
    /**
     * @param  string|array<int, string>  $columns
     */
    public static function filter(string|array $columns, bool $grouped = false): Closure
    {
        return static function (Builder $query, mixed $value) use ($columns, $grouped): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $pattern = '%'.self::escape($value).'%';
            if (is_string($columns) && ! $grouped) {
                $query->whereLike($columns, $pattern);

                return;
            }

            $query->where(static function (Builder $query) use ($columns, $pattern): void {
                foreach ((array) $columns as $column) {
                    $query->orWhereLike($column, $pattern);
                }
            });
        };
    }

    /**
     * Escape SQL LIKE wildcards so % and _ in the value match literally.
     */
    public static function escape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
