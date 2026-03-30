<?php

declare(strict_types=1);

use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

it('normalizes incoming filter values into initial filters for the table', function (): void {
    $initialTableData = [
        'data' => [
            [
                'id' => 5001,
                'name' => 'Constellation Andromeda',
                'manufacturer' => ['name' => 'RSI'],
            ],
        ],
        'meta' => [
            'total' => 1,
        ],
    ];

    $request = [
        'version' => '4.0.0-LIVE',
        'filter' => [
            'manufacturer.name' => [' RSI ', '', 'Drake'],
            'role' => ' Cargo ',
            'crew.min' => [' 2 ', ''],
            'empty' => '   ',
        ],
    ];

    $normalizedFilter = [
        'manufacturer.name' => 'RSI,Drake',
        'role' => 'Cargo',
        'crew.min' => '2',
    ];

    $apiJsonRequest = mock(ApiJsonRequest::class);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest) use ($normalizedFilter): bool {
            return $path === route('vehicles.index', [], false)
                && $apiRequest->query('version') === '4.0.0-LIVE'
                && $apiRequest->query('filter') === $normalizedFilter;
        })
        ->andReturn($initialTableData);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest) use ($normalizedFilter): bool {
            return $path === route('vehicles.filters', [], false)
                && $apiRequest->query('version') === '4.0.0-LIVE'
                && $apiRequest->query('filter') === $normalizedFilter;
        })
        ->andReturn([
            'filters' => [
                'manufacturer' => ['RSI', 'Drake'],
                'role' => ['Cargo'],
            ],
        ]);

    $response = $this->get(route('web.vehicles.index', $request));

    $expectedEndpoint = route('vehicles.index', [
        'version' => '4.0.0-LIVE',
    ]);
    $response->assertSuccessful()
        ->assertViewHas('initialTableData', $initialTableData)
        ->assertViewHas('initialHeaderFilter', [
            'manufacturer' => ['RSI', 'Drake'],
            'role' => ['Cargo'],
        ])
        ->assertViewHas('initialFilters', [
            ['field' => 'manufacturer.name', 'value' => 'RSI,Drake'],
            ['field' => 'role', 'value' => 'Cargo'],
            ['field' => 'crew.min', 'value' => '2'],
        ])
        ->assertSeeText('Vehicles')
        ->assertSee('for="vehicles-api-url"', false)
        ->assertSee('id="vehicles-api-url"', false)
        ->assertSee('value="'.$expectedEndpoint.'"', false)
        ->assertSee('href="'.$expectedEndpoint.'"', false)
        ->assertSeeText('API URL')
        ->assertSeeText('Open');
});

it('exposes no initial filters when request filters are empty', function (): void {
    $apiJsonRequest = mock(ApiJsonRequest::class);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest): bool {
            return $path === route('vehicles.index', [], false)
                && $apiRequest->query('filter') === null;
        })
        ->andReturn(['data' => [], 'meta' => ['total' => 0]]);

    $apiJsonRequest->shouldReceive('request')
        ->once()
        ->ordered()
        ->withArgs(function (string $path, Request $apiRequest): bool {
            return $path === route('vehicles.filters', [], false)
                && $apiRequest->query('filter') === null;
        })
        ->andReturn(['filters' => []]);

    $response = $this->get(route('web.vehicles.index'));

    $response->assertSuccessful()
        ->assertViewHas('initialTableData', ['data' => [], 'meta' => ['total' => 0]])
        ->assertViewHas('initialHeaderFilter', [])
        ->assertViewHas('initialFilters', [])
        ->assertSeeText('Vehicles')
        ->assertSee('for="vehicles-api-url"', false)
        ->assertSee('id="vehicles-api-url"', false)
        ->assertSee('value="'.route('vehicles.index').'"', false)
        ->assertSee('href="'.route('vehicles.index').'"', false)
        ->assertSeeText('API URL')
        ->assertSeeText('Open');
});
