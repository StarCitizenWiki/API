<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Concerns;

use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

trait ComputesFacets
{
    /**
     * The Eloquent model class to build facet queries from.
     */
    abstract protected function facetModelClass(): string;

    /**
     * Facet definitions keyed by output name.
     *
     * Supported keys per facet:
     * - expr: SQL value expression (required)
     * - join: closure(QueryBuilder) to add joins (optional)
     * - cast: closure(mixed) to cast the value (optional)
     * - labelResolver: callable(mixed, ?string) (optional)
     * - label_expr: SQL expression for a separate label column (optional)
     * - group_by: custom GROUP BY expression (default: expr)
     * - order_by: custom ORDER BY expression (default: "expr IS NULL, expr")
     * - count_expr: custom count expression (default: "count(*)")
     * - group_column: column name for FilterValues grouping (optional)
     *
     * @return array<string, array<string, mixed>>
     */
    abstract protected function facetDefinitions(Request $request): array;

    /**
     * FilterCache namespace constant.
     */
    abstract protected function facetCacheNamespace(): string;

    /**
     * FilterCache key for the current request.
     */
    abstract protected function facetCacheKey(Request $request): string;

    /**
     * Base query for facet computation.
     *
     * Override to add version scoping, category filtering, or default joins.
     */
    protected function facetBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for($this->facetModelClass(), $request)
            ->allowedFilters(...$this->allowedFilters());
    }

    /**
     * Filter keys to ignore when checking for effective (cache-busting) filters.
     *
     * @return list<string>
     */
    protected function ignoredFacetFilters(): array
    {
        return [];
    }

    /**
     * Extra facets computed outside the standard loop.
     *
     * @return array<string, mixed>
     */
    protected function extraFacets(Request $request): array
    {
        return [];
    }

    /**
     * Compute all facets with caching.
     */
    protected function computeFacetsResponse(Request $request): JsonResponse
    {
        $resolver = function () use ($request): array {
            $baseQuery = $this->facetBaseQuery($request);
            $facets = $this->facetDefinitions($request);

            $deferKeys = array_flip(['join', 'label_expr', 'count_expr', 'group_by', 'order_by', 'group_column']);

            $computed = $this->computeConsolidatedFacets(
                clone $baseQuery,
                array_filter(
                    $facets,
                    static fn (array $facet): bool => isset($facet['expr']) && array_intersect_key($facet, $deferKeys) === [],
                ),
            );

            $out = [];
            foreach ($facets as $name => $facet) {
                $out[$name] = $computed[$name]
                    ?? $this->computeFacet(clone $baseQuery, $facet);
            }

            return $out + $this->extraFacets($request);
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []), $this->ignoredFacetFilters())) {
            $filters = $resolver();
        } else {
            $filters = FilterCache::rememberForever(
                $this->facetCacheNamespace(),
                $this->facetCacheKey($request),
                $resolver,
            );
        }

        return response()->json([
            'filters' => $filters,
        ]);
    }

    /**
     * @param  array<string, array<string, mixed>>  $facet
     */
    private function computeFacet(QueryBuilder $query, array $facet): array
    {
        $expr = $facet['expr'];
        $groupBy = $facet['group_by'] ?? $expr;
        $orderBy = $facet['order_by'] ?? "{$expr} IS NULL, {$expr}";
        $countExpr = $facet['count_expr'] ?? 'count(*)';

        if (isset($facet['join'])) {
            ($facet['join'])($query);
        }

        $select = [DB::raw("{$expr} as value")];

        if (isset($facet['label_expr'])) {
            $select[] = DB::raw("{$facet['label_expr']} as label");
        }

        $select[] = DB::raw("{$countExpr} as count");

        $rows = $query
            ->select($select)
            ->groupByRaw($groupBy)
            ->orderByRaw($orderBy)
            ->get();

        return FilterValues::fromRows(
            $rows,
            $facet['cast'] ?? null,
            $facet['labelResolver'] ?? null,
            groupColumn: $facet['group_column'] ?? null,
        );
    }

    /**
     * One GROUPING SETS pass over every plain facet.
     *
     * @param  array<string, array<string, mixed>>  $facets
     * @return array<string, array<int, array{value: mixed, label: string, count: int, group?: string}>>
     */
    private function computeConsolidatedFacets(QueryBuilder $query, array $facets): array
    {
        if ($facets === []) {
            return [];
        }

        $names = array_keys($facets);
        $select = [];
        $groupingSets = [];

        foreach ($names as $i => $name) {
            $expr = $facets[$name]['expr'];
            $select[] = DB::raw("{$expr} AS f{$i}");
            $select[] = DB::raw("GROUPING({$expr}) AS f{$i}_grp");
            $groupingSets[] = "({$expr})";
        }

        $select[] = DB::raw('count(*) AS count');

        $rows = $query
            ->select($select)
            ->groupByRaw('GROUPING SETS ('.implode(', ', $groupingSets).')')
            ->get();

        $buckets = array_fill_keys($names, []);

        foreach ($rows as $row) {
            foreach ($names as $i => $name) {
                if (((int) $row->{"f{$i}_grp"}) === 0) {
                    $buckets[$name][] = (object) [
                        'value' => $row->{"f{$i}"},
                        'count' => $row->count,
                    ];

                    break;
                }
            }
        }

        $out = [];

        foreach ($facets as $name => $facet) {
            $out[$name] = FilterValues::fromRows(
                new Collection($buckets[$name]),
                $facet['cast'] ?? null,
                $facet['labelResolver'] ?? null,
            );
        }

        return $out;
    }
}
