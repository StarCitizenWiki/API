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
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('includes game vehicles with slug in URLs', function (): void {
    $vehicle = Vehicle::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'vehicles'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-vehicles.xml'));

    expect($content)->toContain(route('web.vehicles.show', $vehicle->slug));
});

it('includes items with slug in URLs', function (): void {
    $item = Item::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'items'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-items.xml'));

    expect($content)->toContain(route('web.items.show', $item->slug));
});

it('includes items with uuid fallback when slug is null', function (): void {
    $item = Item::factory()->create(['slug' => null]);

    $this->artisan('sitemap:generate', ['--only' => 'items'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-items.xml'));

    expect($content)->toContain(route('web.items.show', $item->uuid));
});

it('includes blueprints with slug in URLs', function (): void {
    $blueprint = Blueprint::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'blueprints'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-blueprints.xml'));

    expect($content)->toContain(route('web.blueprints.show', $blueprint->slug));
});

it('includes blueprints with uuid fallback when slug is null', function (): void {
    $blueprint = Blueprint::factory()->create(['slug' => null]);

    $this->artisan('sitemap:generate', ['--only' => 'blueprints'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-blueprints.xml'));

    expect($content)->toContain(route('web.blueprints.show', $blueprint->uuid));
});

it('includes commodities with slug in URLs', function (): void {
    $commodity = Commodity::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'commodities'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-commodities.xml'));

    expect($content)->toContain(route('web.commodities.show', $commodity->slug));
});

it('includes commodities with uuid fallback when slug is null', function (): void {
    $commodity = Commodity::factory()->create(['slug' => null]);

    $this->artisan('sitemap:generate', ['--only' => 'commodities'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-commodities.xml'));

    expect($content)->toContain(route('web.commodities.show', $commodity->uuid));
});

it('includes missions with slug in URLs', function (): void {
    $mission = Mission::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'missions'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-missions.xml'));

    expect($content)->toContain(route('web.missions.show', $mission->slug));
});

it('includes starmap locations with uuid when slug is null', function (): void {
    $location = StarmapLocation::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'locations'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-locations.xml'));

    expect($content)->toContain(route('web.locations.show', $location->uuid));
});

it('includes starsystems with code in URLs', function (): void {
    $system = Starsystem::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'starsystems'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-starsystems.xml'));

    expect($content)->toContain(route('web.starmap.systems.show', $system->code));
});

it('includes celestial objects with code in URLs', function (): void {
    $object = CelestialObject::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'celestial-objects'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-celestial-objects.xml'));

    expect($content)->toContain(route('web.starmap.celestial-objects.show', $object->code));
});

it('sets priority 1.0 for game data segments', function (): void {
    $item = Item::factory()->create();

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
