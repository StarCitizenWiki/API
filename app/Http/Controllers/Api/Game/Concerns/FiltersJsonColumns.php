<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;

/**
 * Provides JSON column filtering and sorting capabilities for PostgreSQL JSONB columns.
 *
 * This trait enables controllers to filter and sort on nested JSON paths within JSONB columns,
 * with support for type casting and multiple value filtering.
 *
 * Implementing classes must define the table and column names for their JSON data.
 */
trait FiltersJsonColumns
{
    /**
     * Get the database table name containing the JSON column.
     *
     * @return string The table name (e.g., 'game_vehicle_data', 'game_item_data')
     */
    abstract protected function getJsonTableName(): string;

    /**
     * Get the name of the JSON column within the table.
     *
     * @return string The column name (e.g., 'data')
     */
    abstract protected function getJsonColumnName(): string;

    /**
     * Create an AllowedSort for a JSON field path.
     *
     * @param  string  $sortKey  The sort parameter name used in API requests
     * @param  string  $path  Dot-notation JSON path (e.g., 'FlightCharacteristics.Speeds.Scm')
     * @param  string|null  $cast  PostgreSQL cast type ('numeric', 'text', etc.) or null for no casting
     */
    protected function jsonSort(string $sortKey, string $path, ?string $cast = 'numeric'): AllowedSort
    {
        return AllowedSort::callback(
            $sortKey,
            function (Builder $query, bool $descending) use ($path, $cast): Builder {
                $direction = $descending ? 'desc' : 'asc';
                $expression = $this->jsonExpression($path, $cast);

                return $query->orderByRaw("{$expression} {$direction} nulls last");
            }
        );
    }

    /**
     * Build a Laravel JSON column path expression.
     *
     * Converts dot notation to Laravel's arrow notation for JSON queries.
     * Example: 'FlightCharacteristics.Speeds.Scm' becomes 'data->FlightCharacteristics->Speeds->Scm'
     *
     * @param  string  $baseColumn  The base column name (e.g., 'game_vehicle_data.data')
     * @param  string  $path  Dot-notation JSON path
     * @return string Laravel JSON column expression
     */
    protected function laravelJsonColumn(string $baseColumn, string $path): string
    {
        return $baseColumn.'->'.str_replace('.', '->', $path);
    }

    /**
     * Apply filter to a JSON field path with optional PostgreSQL casting.
     *
     * Supports filtering by single or multiple values. Filters out null and empty values.
     * Uses Laravel's JSON where clauses for non-cast queries, or raw PostgreSQL expressions for cast queries.
     *
     * @param  Builder  $query  The Eloquent query builder
     * @param  string  $path  Dot-notation JSON path (e.g., 'FlightCharacteristics.Speeds.Scm')
     * @param  mixed  $value  Single value or array of values to filter by
     * @param  string|null  $cast  PostgreSQL cast type ('numeric', 'text', etc.) or null for no casting
     */
    protected function applyJsonFilter(Builder $query, string $path, mixed $value, ?string $cast = null): void
    {
        $values = is_array($value) ? $value : [$value];
        $values = array_values(array_filter($values, static fn ($item) => $item !== null && $item !== ''));

        if ($values === []) {
            return;
        }

        if ($cast === null || $cast === '') {
            $jsonColumn = $this->laravelJsonColumn($this->getJsonTableName().'.'.$this->getJsonColumnName(), $path);

            $query->whereIn($jsonColumn, $values);

            return;
        }

        $expression = $this->jsonExpression($path, $cast);
        $query->whereIn(DB::raw($expression), $values);
    }

    /**
     * Build a PostgreSQL JSONB path expression with optional casting.
     *
     * Example without cast: (game_vehicle_data.data #>> '{FlightCharacteristics,Speeds,Scm}')
     * Example with cast: ((game_vehicle_data.data #>> '{FlightCharacteristics,Speeds,Scm}')::numeric)
     *
     * @param  string  $path  Dot-notation JSON path
     * @param  string|null  $cast  Cast type (numeric/text) or null for no casting
     * @return string PostgreSQL JSONB expression
     */
    protected function jsonExpression(string $path, ?string $cast = null): string
    {
        $segments = array_map('trim', explode('.', $path));
        $pathExpression = implode(',', $segments);

        $expression = $this->getJsonTableName().'.'.$this->getJsonColumnName()." #>> '{".$pathExpression."}'";

        if ($cast === null || $cast === '') {
            return $expression;
        }

        return sprintf('(%s)::%s', $expression, $cast);
    }
}
