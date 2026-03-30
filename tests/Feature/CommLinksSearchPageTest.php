<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;

uses(RefreshDatabase::class);

it('renders the comm-link search page', function (): void {
    $response = $this->get(route('web.comm-links.search'));

    $response->assertSuccessful();

    $crawler = new Crawler($response->getContent());

    expect($crawler->filter('h1')->text())->toBe('Comm-Link Search')
        ->and($crawler->filter('h2')->each(static fn (Crawler $heading): string => $heading->text()))->toBe([
            'Text Search',
            'Image Search',
        ])
        ->and($crawler->filter('form[action="'.route('web.comm-links.index').'"]')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('form[action="'.route('web.comm-links.images.search').'"]')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('form[action="'.route('web.comm-links.images.reverse-search').'"]')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('input[name="search"]')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('input[name="query"][type="search"]')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('input[name="url"][type="url"]')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('input[name="image"][type="file"]')->count())->toBeGreaterThan(0)
        ->and($crawler->filter('select[name="similarity"] option')->count())->toBeGreaterThan(0);
});
