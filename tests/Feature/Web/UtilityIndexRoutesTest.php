<?php

declare(strict_types=1);

use App\Services\ApiJsonRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

$assertTabulatorPage = function (
    TestCase $testCase,
    string $routeName,
    string $heading,
    string $expectedLabel,
    string $tableId,
    string $apiUrlTargetId,
    string $endpointRouteName,
    array $initialTableData,
    array $headerFilters
): void {
    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->andReturn(
            $initialTableData,
            ['filters' => $headerFilters],
        );

    $response = $testCase->get(route($routeName));

    $response->assertSuccessful();

    $crawler = new Crawler($response->getContent());

    expect($crawler->filter('h1')->text())->toBe($heading)
        ->and($crawler->filter("div[data-tabulator-id=\"{$tableId}\"]")->count())->toBe(1)
        ->and($crawler->filter("input#{$apiUrlTargetId}")->attr('value'))->toBe(route($endpointRouteName))
        ->and($response->getContent())->toContain($expectedLabel)
        ->and($response->getContent())->toContain(route($endpointRouteName));
};

it('renders the tabulator-based index routes', function (
    string $routeName,
    string $heading,
    string $expectedLabel,
    string $tableId,
    string $apiUrlTargetId,
    string $endpointRouteName,
    array $initialTableData,
    array $headerFilters
) use ($assertTabulatorPage): void {
    $assertTabulatorPage(
        $this,
        $routeName,
        $heading,
        $expectedLabel,
        $tableId,
        $apiUrlTargetId,
        $endpointRouteName,
        $initialTableData,
        $headerFilters,
    );
})->with([
    'comm-links' => [
        'routeName' => 'web.comm-links.index',
        'heading' => 'Comm-Links',
        'expectedLabel' => 'Roadmap Roundup',
        'tableId' => 'comm-links-table',
        'apiUrlTargetId' => 'comm-links-api-url',
        'endpointRouteName' => 'comm-links.index',
        'initialTableData' => [
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
        ],
        'headerFilters' => [
            'channel' => ['Comm-Link'],
            'category' => ['Update'],
            'series' => ['Roadmap'],
        ],
    ],
    'galactapedia' => [
        'routeName' => 'web.galactapedia.index',
        'heading' => 'Galactapedia',
        'expectedLabel' => 'ArcCorp',
        'tableId' => 'galactapedia-table',
        'apiUrlTargetId' => 'galactapedia-api-url',
        'endpointRouteName' => 'galactapedia.index',
        'initialTableData' => [
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
        ],
        'headerFilters' => [
            'category' => ['Locations'],
            'tag' => ['Planet'],
            'template' => ['Location'],
        ],
    ],
    'ship-matrix ground vehicles' => [
        'routeName' => 'web.ship-matrix.ground-vehicles.index',
        'heading' => 'Ship-Matrix Vehicles',
        'expectedLabel' => 'Cyclone RC',
        'tableId' => 'ship-matrix-vehicles-table',
        'apiUrlTargetId' => 'ship-matrix-api-url-vehicles',
        'endpointRouteName' => 'shipmatrix.vehicles.index',
        'initialTableData' => [
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
        ],
        'headerFilters' => [
            'manufacturer' => ['Tumbril'],
            'size' => ['Vehicle'],
            'type' => ['Ground Vehicle'],
        ],
    ],
    'ship-matrix vehicles' => [
        'routeName' => 'web.ship-matrix.vehicles.index',
        'heading' => 'Ship-Matrix Vehicles',
        'expectedLabel' => 'Constellation Andromeda',
        'tableId' => 'ship-matrix-vehicles-table',
        'apiUrlTargetId' => 'ship-matrix-api-url-vehicles',
        'endpointRouteName' => 'shipmatrix.vehicles.index',
        'initialTableData' => [
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
        ],
        'headerFilters' => [
            'manufacturer' => ['RSI'],
            'size' => ['Large'],
            'type' => ['Ship'],
        ],
    ],
]);

it('renders the stats index route', function (): void {
    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->andReturn([
            'data' => [
                'funds' => 123456.78,
                'fleet' => 9876,
                'timestamp' => '2025-03-01T10:15:00+00:00',
            ],
        ]);

    $response = $this->get(route('web.stats.index'));

    $response->assertSuccessful();

    $crawler = new Crawler($response->getContent());
    $expectedUpdatedLabel = Carbon::parse('2025-03-01T10:15:00+00:00')->toDayDateTimeString();

    expect($crawler->filter('h1')->text())->toBe('Stats')
        ->and($crawler->filter('.stat-title')->each(static fn ($node): string => trim($node->text())))->toBe(['Funds', 'Fleet'])
        ->and($crawler->filter('.stat-value')->eq(0)->text())->toBe('$123,456.78')
        ->and($crawler->filter('.stat-value')->eq(1)->text())->toBe('9,876')
        ->and($crawler->filter('.stat-desc')->eq(0)->text())->toContain($expectedUpdatedLabel)
        ->and($crawler->filter('.stat-desc')->eq(1)->text())->toContain($expectedUpdatedLabel);
});
