<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('renders seo meta tags on commodity show page', function (): void {
    $commodity = Commodity::factory()->create([
        'name' => 'Agricium',
        'description' => 'A rare and valuable mineral.',
    ]);

    $response = $this->get("/commodities/{$commodity->uuid}");

    $response->assertSuccessful()
        ->assertSee('Agricium', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('og:title', false)
        ->assertSee('og:description', false)
        ->assertSee('twitter:card', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('BreadcrumbList', false);
});

it('renders seo breadcrumbs on commodity show page', function (): void {
    $commodity = Commodity::factory()->create([
        'name' => 'Quantanium',
    ]);

    $response = $this->get("/commodities/{$commodity->uuid}");

    $response->assertSuccessful()
        ->assertSee('All Commodities</a>', false)
        ->assertSee('Quantanium', false);
});

it('renders canonical url with slug and version on commodity show page', function (): void {
    $commodity = Commodity::factory()->create([
        'name' => 'Beryl',
    ]);

    $response = $this->get("/commodities/{$commodity->uuid}?version=4.0.0-LIVE");

    $response->assertSuccessful()
        ->assertSee('rel="canonical"', false)
        ->assertSee('/commodities/'.$commodity->slug, false);
});

it('resolves commodity show page by slug', function (): void {
    $commodity = Commodity::factory()->create([
        'name' => 'Taranite',
    ]);

    $response = $this->get("/commodities/{$commodity->slug}");

    $response->assertSuccessful()
        ->assertSee('Taranite', false)
        ->assertSee('rel="canonical"', false);
});
