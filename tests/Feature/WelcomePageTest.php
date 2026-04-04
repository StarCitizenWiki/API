<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\DomCrawler\Crawler;

uses(RefreshDatabase::class);

function welcomePageCrawler(TestResponse $response): Crawler
{
    return new Crawler($response->getContent());
}

function assertWelcomeLinks(TestResponse $response, array $expectedUrls): void
{
    $crawler = welcomePageCrawler($response);

    foreach ($expectedUrls as $url) {
        expect($crawler->filter(sprintf('a[href="%s"]', $url))->count())->toBeGreaterThan(0);
    }
}

it('renders the welcome page categories for guests', function (): void {
    $response = $this->get(route('home'));
    $crawler = welcomePageCrawler($response);

    $response->assertSuccessful();
    assertWelcomeLinks($response, [
        route('web.items.index'),
        route('web.vehicles.index'),
        route('web.comm-links.search'),
        route('web.starmap.systems.index'),
        route('web.locations.index'),
    ]);

    expect($crawler->filter('[data-testid="welcome-search-items"] .btn.btn-primary')->count())->toBe(1)
        ->and($crawler->filter(sprintf('a[href="%s"]', route('admin.dashboard')))->count())->toBe(0);
});

it('shows the admin link for authorized users', function (): void {
    $user = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertSuccessful();
    assertWelcomeLinks($response, [
        route('home'),
        route('admin.dashboard'),
    ]);
});

it('hides the admin card for authenticated non-admin users', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertSuccessful();
    expect(welcomePageCrawler($response)->filter(sprintf('a[href="%s"]', route('admin.dashboard')))->count())->toBe(0);
});
