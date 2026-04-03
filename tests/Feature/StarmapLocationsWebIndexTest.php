<?php

declare(strict_types=1);

use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

it('normalizes incoming starmap location filters into initial filters for the table', function (): void {
    $initialTableData = [
        'data' => [
            [
                'uuid' => fake()->uuid(),
                'name' => 'Baijini Point',
                'system_name' => 'Stanton',
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
            'system_name' => ' Stan ',
            'hide_in_world' => [' true ', ''],
            'empty' => '   ',
        ],
    ];

    $normalizedFilter = [
        'type_name' => 'Station',
        'system_name' => 'Stan',
        'hide_in_world' => 'true',
    ];

    $apiJsonRequest = mock(ApiJsonRequest::class);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest) use ($normalizedFilter): bool {
            return $path === route('starmap-locations.index', [], false)
                && $apiRequest->query('version') === '4.1.0-LIVE'
                && $apiRequest->query('filter') === $normalizedFilter;
        })
        ->andReturn($initialTableData);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest) use ($normalizedFilter): bool {
            return $path === route('starmap-locations.filters', [], false)
                && $apiRequest->query('version') === '4.1.0-LIVE'
                && $apiRequest->query('filter') === $normalizedFilter;
        })
        ->andReturn([
            'filters' => [
                'system_name' => [
                    ['value' => 'Stanton', 'label' => 'Stanton'],
                ],
                'parent_name' => [
                    ['value' => 'ArcCorp', 'label' => 'ArcCorp'],
                ],
                'type_name' => [
                    ['value' => 'Station', 'label' => 'Station'],
                ],
            ],
        ]);

    $response = $this->get(route('web.starmap.locations.index', $request));

    $expectedEndpoint = route('starmap-locations.index', [
        'version' => '4.1.0-LIVE',
    ]);

    $response->assertSuccessful()
        ->assertViewHas('initialTableData', $initialTableData)
        ->assertViewHas('initialHeaderFilter', [
            'system_name' => [
                ['value' => 'Stanton', 'label' => 'Stanton'],
            ],
            'parent_name' => [
                ['value' => 'ArcCorp', 'label' => 'ArcCorp'],
            ],
            'type_name' => [
                ['value' => 'Station', 'label' => 'Station'],
            ],
        ])
        ->assertViewHas('headerFilterOptionsMap', fn (array $map): bool => $map['system_name'] === 'system_name'
            && $map['parent_name'] === 'parent_name')
        ->assertViewHas('initialFilters', [
            ['field' => 'type_name', 'value' => 'Station'],
            ['field' => 'system_name', 'value' => 'Stan'],
            ['field' => 'hide_in_world', 'value' => 'true'],
        ])
        ->assertSeeText('Starmap Locations')
        ->assertSee('for="starmap-locations-api-url"', false)
        ->assertSee('id="starmap-locations-api-url"', false)
        ->assertSee('value="'.$expectedEndpoint.'"', false)
        ->assertSee('href="'.$expectedEndpoint.'"', false)
        ->assertSeeText('API URL')
        ->assertSeeText('Open');
});

it('exposes no initial filters when starmap location request filters are empty', function (): void {
    $apiJsonRequest = mock(ApiJsonRequest::class);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest): bool {
            return $path === route('starmap-locations.index', [], false)
                && $apiRequest->query('filter') === null;
        })
        ->andReturn(['data' => [], 'meta' => ['total' => 0]]);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest): bool {
            return $path === route('starmap-locations.filters', [], false)
                && $apiRequest->query('filter') === null;
        })
        ->andReturn(['filters' => []]);

    $response = $this->get(route('web.starmap.locations.index'));

    $response->assertSuccessful()
        ->assertViewHas('initialTableData', ['data' => [], 'meta' => ['total' => 0]])
        ->assertViewHas('initialHeaderFilter', [])
        ->assertViewHas('initialFilters', [])
        ->assertSeeText('Starmap Locations')
        ->assertSee('for="starmap-locations-api-url"', false)
        ->assertSee('id="starmap-locations-api-url"', false)
        ->assertSee('value="'.route('starmap-locations.index').'"', false)
        ->assertSee('href="'.route('starmap-locations.index').'"', false)
        ->assertSeeText('API URL')
        ->assertSeeText('Open');
});
