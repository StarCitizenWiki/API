<?php

declare(strict_types=1);

use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

function expectStarmapIndexRequests(
    ?string $version,
    ?array $normalizedFilter,
    array $indexResponse,
): void {
    $apiJsonRequest = mock(ApiJsonRequest::class);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest) use ($normalizedFilter, $version): bool {
            return $path === route('locations.index', [], false)
                && $apiRequest->query('version') === $version
                && $apiRequest->query('filter') === $normalizedFilter;
        })
        ->andReturn($indexResponse);
}

it('normalizes incoming starmap location filters into initial filters for the table', function (): void {
    $initialTableData = [
        'data' => [
            [
                'uuid' => $uuid = fake()->uuid(),
                'name' => 'Baijini Point',
                'web_url' => route('web.locations.show', ['identifier' => $uuid, 'version' => '4.1.0-LIVE']),
                'system' => 'Stanton',
                'amenities' => [
                    [
                        'display_name' => 'Clinic',
                        'name' => 'Clinic',
                    ],
                ],
                'child_count' => 2,
            ],
        ],
        'meta' => [
            'total' => 1,
        ],
    ];

    $request = [
        'version' => '4.1.0-LIVE',
        'filter' => [
            'type_name' => [' Station ', ''],
            'system' => ' Stan ',
            'block_travel' => [' true ', ''],
            'empty' => '   ',
        ],
    ];

    $normalizedFilter = [
        'type_name' => 'Station',
        'system' => 'Stan',
        'block_travel' => 'true',
    ];

    expectStarmapIndexRequests(
        '4.1.0-LIVE',
        $normalizedFilter,
        $initialTableData,
    );

    $response = $this->get(route('web.locations.index', $request));

    $expectedEndpoint = route('locations.index', [
        'version' => '4.1.0-LIVE',
    ]);

    $response->assertSuccessful()
        ->assertViewHas('initialTableData', $initialTableData)
        ->assertViewHas('initialHeaderFilter', [])
        ->assertViewHas('headerFilterOptionsMap', fn (array $map): bool => $map['system'] === 'system'
            && $map['parent.name'] === 'parent_name'
            && $map['type.classification'] === 'type_classification'
            && $map['respawn_location_type'] === 'respawn_location_type'
            && $map['jurisdiction.name'] === 'jurisdiction_name'
            && $map['affiliation.name'] === 'affiliation_name'
            && $map['amenities'] === 'amenity'
            && $map['has_resources'] === 'has_resources')
        ->assertViewHas('initialFilters', [
            ['field' => 'type_name', 'value' => 'Station'],
            ['field' => 'system', 'value' => 'Stan'],
            ['field' => 'block_travel', 'value' => 'true'],
        ])
        ->assertSee('value="'.$expectedEndpoint.'"', false)
        ->assertSee('"urlField":"web_url"', false);
});

it('exposes no initial filters when starmap location request filters are empty', function (): void {
    expectStarmapIndexRequests(
        null,
        null,
        ['data' => [], 'meta' => ['total' => 0]],
    );

    $response = $this->get(route('web.locations.index'));

    $response->assertSuccessful()
        ->assertViewHas('initialTableData', ['data' => [], 'meta' => ['total' => 0]])
        ->assertViewHas('initialHeaderFilter', [])
        ->assertViewHas('initialFilters', [])
        ->assertSee('value="'.route('locations.index').'"', false);
});
