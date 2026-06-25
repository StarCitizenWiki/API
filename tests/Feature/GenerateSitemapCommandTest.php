<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Item;
use App\Models\Game\Mission\Mission;
use App\Models\Game\StarmapLocation;
use App\Models\Game\Vehicle;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Console\Command;

afterEach(function (): void {
    foreach (glob(storage_path('app/sitemaps/sitemap*.xml')) as $file) {
        unlink($file);
    }
});

it('generates all sitemap segments when no --only flag is provided', function (): void {
    Channel::factory()->create(['name' => 'General']);
    CommLink::factory()->create();
    Article::factory()->create();

    $this->artisan('sitemap:generate')
        ->assertExitCode(Command::SUCCESS);

    foreach ([
        'sitemap.xml',
        'sitemap-static.xml',
        'sitemap-comm-links.xml',
        'sitemap-galactapedia.xml',
        'sitemap-vehicles.xml',
        'sitemap-items.xml',
        'sitemap-blueprints.xml',
        'sitemap-commodities.xml',
        'sitemap-missions.xml',
        'sitemap-locations.xml',
        'sitemap-starsystems.xml',
        'sitemap-celestial-objects.xml',
    ] as $file) {
        expect(storage_path('app/sitemaps/'.$file))->toBeFile();
    }
});

it('generates only specified segments with --only flag', function (): void {
    Channel::factory()->create(['name' => 'General']);
    CommLink::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'comm-links'])
        ->assertExitCode(Command::SUCCESS);

    expect(storage_path('app/sitemaps/sitemap-comm-links.xml'))->toBeFile();
    expect(storage_path('app/sitemaps/sitemap.xml'))->toBeFile();
    expect(storage_path('app/sitemaps/sitemap-static.xml'))->not->toBeFile();
    expect(storage_path('app/sitemaps/sitemap-galactapedia.xml'))->not->toBeFile();
});

it('generates multiple segments with comma-separated --only flag', function (): void {
    Channel::factory()->create(['name' => 'General']);
    CommLink::factory()->create();
    Article::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'comm-links,galactapedia'])
        ->assertExitCode(Command::SUCCESS);

    expect(storage_path('app/sitemaps/sitemap-comm-links.xml'))->toBeFile();
    expect(storage_path('app/sitemaps/sitemap-galactapedia.xml'))->toBeFile();
    expect(storage_path('app/sitemaps/sitemap-static.xml'))->not->toBeFile();
});

it('fails with invalid segment names', function (): void {
    $this->artisan('sitemap:generate', ['--only' => 'invalid'])
        ->assertExitCode(Command::FAILURE);
});

it('generates sitemap index linking to all segment files', function (): void {
    Channel::factory()->create(['name' => 'General']);
    CommLink::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'static,comm-links'])
        ->assertExitCode(Command::SUCCESS);

    $indexContent = file_get_contents(storage_path('app/sitemaps/sitemap.xml'));

    expect($indexContent)->toContain('sitemaps/sitemap-static.xml');
    expect($indexContent)->toContain('sitemaps/sitemap-comm-links.xml');
});

it('includes static routes in the static sitemap', function (): void {
    $this->artisan('sitemap:generate', ['--only' => 'static'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-static.xml'));

    expect($content)->toContain(route('home'));
    expect($content)->toContain(route('web.comm-links.index'));
    expect($content)->toContain(route('web.vehicles.index'));
    expect($content)->toContain(route('web.items.index'));
    expect($content)->toContain(route('web.starmap.systems.index'));
});

it('includes comm-link URLs using cig_id', function (): void {
    Channel::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'comm-links'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-comm-links.xml'));

    expect($content)->toContain(route('web.comm-links.show', $commLink->cig_id));
});

it('excludes subscriber-only comm-links', function (): void {
    Channel::factory()->create(['name' => 'General']);
    $public = CommLink::factory()->create();
    Channel::factory()->create(['name' => 'Subscriber']);
    CommLink::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'comm-links'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-comm-links.xml'));

    expect($content)->toContain(route('web.comm-links.show', $public->cig_id));
    expect($content)->not->toContain('<loc>'.count(CommLink::withoutGlobalScope('limit_subscriber')->get()).'</loc>');
});

it('excludes disabled galactapedia articles', function (): void {
    $enabled = Article::factory()->create(['disabled' => false]);
    Article::factory()->create(['disabled' => true]);

    $this->artisan('sitemap:generate', ['--only' => 'galactapedia'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-galactapedia.xml'));

    expect($content)->toContain(route('web.galactapedia.show', $enabled->cig_id));
    $disabled = Article::where('disabled', true)->first();
    expect($content)->not->toContain(route('web.galactapedia.show', $disabled->cig_id));
});

it('includes slug/uuid/code models in sitemap URLs', function (string $segment, string $route, string $model, string $param): void {
    // When slug is set the sitemap uses it; when null it falls back to uuid.
    // Starsystem and CelestialObject use code. StarmapLocation and Mission use uuid.
    $instance = $model::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => $segment])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-'.$segment.'.xml'));

    expect($content)->toContain(route($route, $instance->$param));
})->with([
    'vehicles' => ['vehicles',          'web.vehicles.show',                    Vehicle::class,          'slug'],
    'items' => ['items',             'web.items.show',                       Item::class,             'slug'],
    'blueprints' => ['blueprints',        'web.blueprints.show',                  Blueprint::class,        'slug'],
    'commodities' => ['commodities',       'web.commodities.show',                 Commodity::class,        'slug'],
    'missions' => ['missions',          'web.missions.show',                    Mission::class,          'slug'],
    'locations' => ['locations',          'web.locations.show',                   StarmapLocation::class,  'uuid'],
    'starsystems' => ['starsystems',       'web.starmap.systems.show',             Starsystem::class,       'code'],
    'celestial objects' => ['celestial-objects', 'web.starmap.celestial-objects.show', CelestialObject::class, 'code'],
]);

it('falls back to uuid when slug is null', function (string $segment, string $route, string $model): void {
    $instance = $model::factory()->create(['slug' => null]);

    $this->artisan('sitemap:generate', ['--only' => $segment])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-'.$segment.'.xml'));

    expect($content)->toContain(route($route, $instance->uuid));
})->with([
    'items' => ['items',       'web.items.show',       Item::class],
    'blueprints' => ['blueprints',  'web.blueprints.show',  Blueprint::class],
    'commodities' => ['commodities', 'web.commodities.show', Commodity::class],
]);

it('sets priority 1.0 for game data segments', function (): void {
    Item::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'items'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-items.xml'));

    expect($content)->toContain('<priority>1.0</priority>');
});

it('sets priority 0.8 for comm-links and galactapedia', function (): void {
    Channel::factory()->create(['name' => 'General']);
    CommLink::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'comm-links'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-comm-links.xml'));

    expect($content)->toContain('<priority>0.8</priority>');
});

it('sets priority 0.5 for starmap segments', function (): void {
    Starsystem::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'starsystems'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-starsystems.xml'));

    expect($content)->toContain('<priority>0.5</priority>');
});
