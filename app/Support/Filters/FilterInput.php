<?php

declare(strict_types=1);

namespace App\Support\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validates index/filters endpoint query params. Laravel cannot match dotted
 * rule keys (filter.crew.min) against literal query keys (filter[crew.min]),
 * so the filter bag is undotted for validation only; the query builder keeps
 * reading the untouched request input.
 */
class FilterInput
{
    /**
     * @param  array<string, mixed>  $rules
     */
    public static function validate(Request $request, array $rules): void
    {
        $data = $request->all();

        if (is_array($data['filter'] ?? null)) {
            $undotted = Arr::undot($data['filter']);

            // When a dotted key collides with a scalar key (filter[crew.min] +
            // filter[crew]), undot silently drops or overwrites values, so the
            // validated data no longer matches the input the query builder
            // reads. Reject the request instead of letting junk reach the DB.
            if (self::leafCount($data['filter']) !== self::leafCount($undotted)) {
                throw ValidationException::withMessages([
                    'filter' => 'The filter parameters are ambiguous.',
                ]);
            }

            $data['filter'] = $undotted;
        }

        Validator::make($data, $rules)->validate();
    }

    /**
     * @param  array<mixed, mixed>  $array
     */
    private static function leafCount(array $array): int
    {
        $count = 0;

        foreach ($array as $value) {
            $count += is_array($value) ? self::leafCount($value) : 1;
        }

        return $count;
    }
}
