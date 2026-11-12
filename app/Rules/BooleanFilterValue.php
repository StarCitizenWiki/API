<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Boolean filter value. Accepts the spellings Spatie casts and Postgres
 * boolean columns take: true/false/1/0, any case. Scalar values are split
 * on the query-builder delimiter so multiselect widgets can send comma
 * lists (`true,false`), matching how spatie's exact filters apply whereIn.
 * Array values (filter[x][]) are validated per element; empty arrays pass
 * because the filter callbacks ignore empty filters. The scalar option is
 * for callbacks that parse one PHP boolean and do not support lists.
 */
class BooleanFilterValue implements ValidationRule
{
    public function __construct(private readonly bool $scalarPhpBoolean = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $delimiter = (string) config('query-builder.delimiter', ',');

        if ($this->scalarPhpBoolean) {
            if ($value === null || $value === '') {
                return;
            }

            if (! is_scalar($value) || ($delimiter !== '' && str_contains((string) $value, $delimiter))) {
                $fail('The :attribute filter must be true or false.');

                return;
            }
        }

        $entries = is_array($value) ? array_values($value) : [$value];

        foreach ($entries as $entry) {
            if (is_bool($entry)) {
                continue;
            }

            if (! is_scalar($entry)) {
                $fail('The :attribute filter must be true or false.');

                return;
            }

            $items = $delimiter === '' ? [(string) $entry] : explode($delimiter, (string) $entry);

            foreach ($items as $item) {
                $valid = $this->scalarPhpBoolean
                    ? trim($item) !== '' && filter_var($item, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null
                    : in_array(strtolower($item), ['true', 'false', '1', '0'], true);

                if (! $valid) {
                    $fail('The :attribute filter must be true or false.');

                    return;
                }
            }
        }
    }
}
