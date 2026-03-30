<?php

declare(strict_types=1);

use App\Services\ApiJsonRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('renders the vehicles index shell and initial payload', function (): void {
    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->andReturn(
            [
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
            [
                'filters' => [
                    'manufacturer' => ['RSI'],
                ],
            ]
        );

    $response = $this->get(route('web.vehicles.index'));

    $response->assertSuccessful();

    $crawler = new Crawler($response->getContent());

    expect($crawler->filter('h1')->text())->toBe('Vehicles')
        ->and($crawler->filter('div[data-tabulator-id="vehicles-table"]')->count())->toBe(1)
        ->and($crawler->filter('input#vehicles-api-url')->attr('value'))->toBe(route('vehicles.index'))
        ->and($response->getContent())->toContain('Constellation Andromeda')
        ->and($response->getContent())->toContain('RSI');
});
