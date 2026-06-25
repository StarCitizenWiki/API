<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;

/**
 * Assert a /filters response's facets match, independent of element order.
 * Facet values are emitted in hash-aggregate order, which is not sorted.
 *
 * @param  array<string, array<int, array{value: mixed, label: string, count: int}>>  $expected
 */
function assertFacetsEqual(TestResponse $response, array $expected): void
{
    $actual = $response->json('filters', []);

    expect(collect($actual)->keys()->sort()->values()->all())
        ->toBe(collect($expected)->keys()->sort()->values()->all(), 'facet keys');

    $sortKey = static fn (array $v): string => $v['value'] === null ? "\0" : (string) $v['value'];

    foreach ($expected as $facet => $values) {
        $actualSorted = collect($actual[$facet] ?? [])
            ->sortBy($sortKey)
            ->values()
            ->all();
        $expectedSorted = collect($values)
            ->sortBy($sortKey)
            ->values()
            ->all();
        expect($actualSorted)->toBe($expectedSorted, "facet [{$facet}]");
    }
}
