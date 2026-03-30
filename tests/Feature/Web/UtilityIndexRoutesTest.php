<?php

declare(strict_types=1);

use App\Services\ApiJsonRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('renders the comm-links index route', function (): void {
    $initialTableData = [
        'data' => [
            [
                'id' => 1001,
                'title' => 'Roadmap Roundup',
                'images_count' => 3,
                'links_count' => 4,
            ],
        ],
        'meta' => [
            'total' => 1,
        ],
    ];

    $headerFilters = [
        'channel' => ['Comm-Link'],
        'category' => ['Update'],
        'series' => ['Roadmap'],
    ];

    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->twice()
        ->andReturn(
            $initialTableData,
            ['filters' => $headerFilters],
        );

    $response = $this->get(route('web.comm-links.index'));

    $response->assertOk()
        ->assertViewIs('comm-links.index')
        ->assertViewHas('initialTableData', function (array $payload): bool {
            return ($payload['data'][0]['id'] ?? null) === 1001
                && ($payload['data'][0]['title'] ?? null) === 'Roadmap Roundup';
        })
        ->assertViewHas('initialHeaderFilter', $headerFilters);
});

it('renders the galactapedia index route', function (): void {
    $initialTableData = [
        'data' => [
            [
                'id' => 'SC-001',
                'title' => 'ArcCorp',
                'category' => 'Locations',
            ],
        ],
        'meta' => [
            'total' => 1,
        ],
    ];

    $headerFilters = [
        'category' => ['Locations'],
        'tag' => ['Planet'],
        'template' => ['Location'],
    ];

    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->twice()
        ->andReturn(
            $initialTableData,
            ['filters' => $headerFilters],
        );

    $response = $this->get(route('web.galactapedia.index'));

    $response->assertOk()
        ->assertViewIs('galactapedia.index')
        ->assertViewHas('initialTableData', function (array $payload): bool {
            return ($payload['data'][0]['id'] ?? null) === 'SC-001'
                && ($payload['data'][0]['title'] ?? null) === 'ArcCorp';
        })
        ->assertViewHas('initialHeaderFilter', $headerFilters);
});

it('renders the ship-matrix ground vehicles index route', function (): void {
    $initialTableData = [
        'data' => [
            [
                'id' => 7001,
                'name' => 'Cyclone RC',
                'manufacturer' => ['name' => 'Tumbril'],
            ],
        ],
        'meta' => [
            'total' => 1,
        ],
    ];

    $headerFilters = [
        'manufacturer' => ['Tumbril'],
        'size' => ['Vehicle'],
        'type' => ['Ground Vehicle'],
    ];

    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->twice()
        ->andReturn(
            $initialTableData,
            ['filters' => $headerFilters],
        );

    $response = $this->get(route('web.ship-matrix.ground-vehicles.index'));

    $response->assertOk()
        ->assertViewIs('ship-matrix.index')
        ->assertViewHas('initialTableData', function (array $payload): bool {
            return ($payload['data'][0]['id'] ?? null) === 7001
                && ($payload['data'][0]['name'] ?? null) === 'Cyclone RC';
        })
        ->assertViewHas('initialHeaderFilter', $headerFilters);
});

it('renders the ship-matrix vehicles index route', function (): void {
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

    $headerFilters = [
        'manufacturer' => ['RSI'],
        'size' => ['Large'],
        'type' => ['Ship'],
    ];

    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->twice()
        ->andReturn(
            $initialTableData,
            ['filters' => $headerFilters],
        );

    $response = $this->get(route('web.ship-matrix.vehicles.index'));

    $response->assertOk()
        ->assertViewIs('ship-matrix.index')
        ->assertViewHas('initialTableData', function (array $payload): bool {
            return ($payload['data'][0]['id'] ?? null) === 5001
                && ($payload['data'][0]['name'] ?? null) === 'Constellation Andromeda';
        })
        ->assertViewHas('initialHeaderFilter', $headerFilters);
});

it('renders the stats index route', function (): void {
    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->once()
        ->andReturn([
            'data' => [
                'funds' => 123456.78,
                'fleet' => 9876,
                'timestamp' => '2025-03-01T10:15:00+00:00',
            ],
        ]);

    $response = $this->get(route('web.stats.index'));

    $response->assertOk()
        ->assertViewIs('stats.index')
        ->assertViewHas('latestStats', function (array $latestStats): bool {
            return ($latestStats['funds'] ?? null) === 123456.78
                && ($latestStats['fleet'] ?? null) === 9876
                && ($latestStats['timestamp'] ?? null) === '2025-03-01T10:15:00+00:00';
        })
        ->assertSee('$123,456.78')
        ->assertSee('9,876');
});
