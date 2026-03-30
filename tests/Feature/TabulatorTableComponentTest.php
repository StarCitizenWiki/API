<?php

declare(strict_types=1);

use Illuminate\Testing\TestView;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

function renderTabulatorTable(TestCase $testCase, array $config, bool $showApiUrl = true): TestView
{
    $showApiUrlAttribute = $showApiUrl ? '' : ' :show-api-url="false"';

    return (fn (): TestView => $this->blade(
        "<x-tabulator-table id=\"example\" :config=\"\$config\"{$showApiUrlAttribute} />",
        ['config' => $config]
    ))->call($testCase);
}

function tabulatorCrawler(TestView $view): Crawler
{
    return new Crawler((string) $view);
}

it('renders the api url controls and tabulator mount when configured', function (): void {
    $view = renderTabulatorTable($this, [
        'endpoint' => '/api/example',
        'apiUrlTargetId' => 'example-api-url',
    ]);

    $crawler = tabulatorCrawler($view);

    expect($crawler->filter('label[for="example-api-url"]')->count())->toBe(1)
        ->and($crawler->filter('input#example-api-url')->count())->toBe(1)
        ->and($crawler->filter('input#example-api-url')->attr('value'))->toBe('/api/example')
        ->and($crawler->filter('a[href="/api/example"]')->count())->toBe(1)
        ->and($crawler->filter('#example[data-tabulator-id="example"]')->count())->toBe(1)
        ->and($crawler->filter('#example-config')->count())->toBe(1);
});

it('can hide the api url block while still rendering the table mount and config', function (): void {
    $view = renderTabulatorTable($this, [
        'endpoint' => '/api/example',
        'apiUrlTargetId' => 'example-api-url',
    ], false);

    $crawler = tabulatorCrawler($view);

    expect($crawler->filter('label[for="example-api-url"]')->count())->toBe(0)
        ->and($crawler->filter('input#example-api-url')->count())->toBe(0)
        ->and($crawler->filter('a[href="/api/example"]')->count())->toBe(0)
        ->and($crawler->filter('#example[data-tabulator-id="example"]')->count())->toBe(1)
        ->and($crawler->filter('#example-config')->count())->toBe(1);
});

it('omits the api url block when no target id is configured', function (): void {
    $view = renderTabulatorTable($this, [
        'endpoint' => '/api/example',
    ]);

    $crawler = tabulatorCrawler($view);

    expect($crawler->filter('label[for="example-api-url"]')->count())->toBe(0)
        ->and($crawler->filter('input#example-api-url')->count())->toBe(0)
        ->and($crawler->filter('a[href="/api/example"]')->count())->toBe(0)
        ->and($crawler->filter('#example[data-tabulator-id="example"]')->count())->toBe(1)
        ->and($crawler->filter('#example-config')->count())->toBe(1);
});
