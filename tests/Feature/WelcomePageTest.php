<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;

uses(RefreshDatabase::class);

it('renders the welcome page categories for guests', function (): void {
    $response = $this->get(route('home'));

    $response->assertSuccessful();

    $crawler = new Crawler($response->getContent());

    expect($crawler->filter('h1')->text())->toBe('Star Citizen Wiki API')
        ->and($crawler->filter('form[action="'.route('web.items.index').'"]')->count())->toBeGreaterThan(0)
        ->and($crawler->filterXPath('//a[@href="'.route('admin.dashboard').'"]')->count())->toBe(0);
});

it('shows the admin link for authorized users', function (): void {
    $user = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertSuccessful();

    $crawler = new Crawler($response->getContent());

    expect($crawler->filterXPath('//a[@href="'.route('admin.dashboard').'"]')->count())->toBeGreaterThan(0);
});
