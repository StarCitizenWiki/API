<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * UUID filter value. Comma-separated lists are allowed; each item must be a
 * UUID because the value is bound to a uuid column. Array values
 * (filter[x][]) are validated per element; empty arrays pass because the
 * filter callbacks ignore empty filters.
 */
class UuidFilterValue implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $entries = is_array($value) ? array_values($value) : [$value];

        foreach ($entries as $entry) {
            if (! is_scalar($entry)) {
                $fail('The :attribute filter must be a valid UUID.');

                return;
            }

            foreach (explode(',', (string) $entry) as $item) {
                if (! Str::isUuid(trim($item))) {
                    $fail('The :attribute filter must be a valid UUID.');

                    return;
                }
            }
        }
    }
}
