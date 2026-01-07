<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class DateFilter implements Filter
{
    public function __construct(public readonly string $column) {}

    /**
     * @param  mixed  $value
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        $value = trim($value);

        if ($value === '') {
            return;
        }

        if (preg_match('/^\d{4}$/', $value) === 1) {
            $query->whereYear($this->column, (int) $value);

            return;
        }

        if (preg_match('/^\d{4}-\d{1,2}$/', $value) === 1) {
            try {
                $start = Carbon::createFromFormat('Y-m', $value)->startOfMonth();
                $end = (clone $start)->endOfMonth();

                $query->whereBetween($this->column, [$start, $end]);
            } catch (Exception $e) {
            }

            return;
        }

        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value) === 1) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $value);
                $query->whereDate($this->column, $date->toDateString());
            } catch (Exception $e) {
            }
        }
    }
}
