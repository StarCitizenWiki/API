<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Numeric filter value. Items are split on the query-builder delimiter;
 * each may carry one comparison operator. In integers-only mode, decimal
 * points and scientific notation are rejected because Postgres refuses
 * them on integer columns; float columns accept both. Integer values must also fit the constrained
 * column's range: Postgres rejects out-of-range literals at bind time with
 * an "integer out of range" error, which would surface as HTTP 500 instead
 * of a 422 validation failure. Array values (filter[x][]) are validated per
 * element; empty arrays pass because the filter callbacks ignore empty
 * filters.
 */
class NumericFilterValue implements ValidationRule
{
    private const OPERATORS = ['>=', '<=', '<>', '>', '<', '='];

    private const INT4_MAX = '2147483647';

    private const INT4_MIN = '2147483648';

    private const INT8_MAX = '9223372036854775807';

    private const INT8_MIN = '9223372036854775808';

    public function __construct(
        private readonly bool $allowOperators = true,
        private readonly bool $integersOnly = false,
        private readonly bool $bigIntColumn = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $entries = is_array($value) ? array_values($value) : [$value];
        $delimiter = (string) config('query-builder.delimiter', ',');

        foreach ($entries as $entry) {
            if (! is_scalar($entry)) {
                $fail($this->message());

                return;
            }

            $items = $delimiter === '' ? [(string) $entry] : explode($delimiter, (string) $entry);

            foreach ($items as $item) {
                if ($this->allowOperators) {
                    foreach (self::OPERATORS as $operator) {
                        if (str_starts_with($item, $operator)) {
                            $item = substr($item, strlen($operator));

                            break;
                        }
                    }
                }

                if (! is_numeric($item) || ($this->integersOnly && (str_contains($item, 'e') || str_contains($item, 'E') || str_contains($item, '.')))) {
                    $fail($this->message());

                    return;
                }

                if ($this->integersOnly && ! $this->fitsIntegerColumn($item)) {
                    $fail($this->message());

                    return;
                }
            }
        }
    }

    /**
     * Compare digit lengths instead of casting to float: values near the
     * int8 bounds lose precision as floats and would pass the check.
     *
     * @param  string  $item  integer digit string, optional sign, already checked by is_numeric
     */
    private function fitsIntegerColumn(string $item): bool
    {
        [$minDigits, $maxDigits] = $this->bigIntColumn
            ? [self::INT8_MIN, self::INT8_MAX]
            : [self::INT4_MIN, self::INT4_MAX];

        $negative = str_starts_with($item, '-');
        $digits = ltrim(ltrim(trim($item), '+-'), '0');

        if ($digits === '') {
            return true;
        }

        $bound = $negative ? $minDigits : $maxDigits;

        return strlen($digits) < strlen($bound)
            || (strlen($digits) === strlen($bound) && strcmp($digits, $bound) <= 0);
    }

    private function message(): string
    {
        return $this->integersOnly
            ? 'The :attribute filter accepts whole numbers'.($this->allowOperators ? ' with optional comparison operators (> >= < <= <>)' : '').', separated by commas.'
            : 'The :attribute filter accepts numbers'.($this->allowOperators ? ' with optional comparison operators (> >= < <= <>)' : '').', separated by commas.';
    }
}
