<?php

declare(strict_types=1);

namespace App\Support;

class QueryLike
{
    /**
     * Escape SQL LIKE wildcards so % and _ in the value match literally.
     */
    public static function escape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
