<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

afterEach(function (): void {
    foreach (glob(storage_path('app/sitemaps/sitemap*.xml')) as $file) {
        unlink($file);
    }
});

it('generates all sitemap segments when no --only flag is provided', function (): void {
    createDefaultGameVersion();
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
    ] as $file) {
        expect(storage_path('app/sitemaps/'.$file))->toBeFile();
    }

    expect(storage_path('app/sitemaps/sitemap-starsystems.xml'))->not->toBeFile();
    expect(storage_path('app/sitemaps/sitemap-celestial-objects.xml'))->not->toBeFile();
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

it('includes slug/uuid/code models in sitemap URLs', function (string $segment, string $route, string $model, ?string $dataModel, ?string $foreignKey): void {
    $version = createDefaultGameVersion();

    // When slug is set the sitemap uses it; when null it falls back to uuid.
    $instance = $model::factory()->create();
    if ($dataModel !== null) {
        $dataModel::factory()->create([
            $foreignKey => $instance->id,
            'game_version_id' => $version->id,
        ]);
    }

    $this->artisan('sitemap:generate', ['--only' => $segment])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-'.$segment.'.xml'));

    expect($content)->toContain(route($route, $instance->slug ?? $instance->uuid));
})->with([
    'vehicles' => ['vehicles', 'web.vehicles.show', Vehicle::class, VehicleData::class, 'vehicle_id'],
    'items' => ['items', 'web.items.show', Item::class, ItemData::class, 'item_id'],
    'blueprints' => ['blueprints', 'web.blueprints.show', Blueprint::class, BlueprintData::class, 'blueprint_id'],
    'commodities' => ['commodities', 'web.commodities.show', Commodity::class, null, null],
    'missions' => ['missions', 'web.missions.show', Mission::class, MissionData::class, 'mission_id'],
    'locations' => ['locations', 'web.locations.show', StarmapLocation::class, StarmapLocationData::class, 'starmap_location_id'],
]);

it('falls back to uuid when slug is null', function (string $segment, string $route, string $model, ?string $dataModel, ?string $foreignKey): void {
    $version = createDefaultGameVersion();

    $instance = $model::factory()->create(['slug' => null]);
    if ($dataModel !== null) {
        $dataModel::factory()->create([
            $foreignKey => $instance->id,
            'game_version_id' => $version->id,
        ]);
    }

    $this->artisan('sitemap:generate', ['--only' => $segment])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-'.$segment.'.xml'));

    expect($content)->toContain(route($route, $instance->uuid));
})->with([
    'items' => ['items', 'web.items.show', Item::class, ItemData::class, 'item_id'],
    'blueprints' => ['blueprints', 'web.blueprints.show', Blueprint::class, BlueprintData::class, 'blueprint_id'],
    'commodities' => ['commodities', 'web.commodities.show', Commodity::class, null, null],
]);

it('excludes entities without data in the default game version', function (string $segment, string $route, string $model, string $dataModel, string $foreignKey): void {
    $default = createDefaultGameVersion();
    $other = GameVersion::factory()->create();

    $current = $model::factory()->create();
    $dataModel::factory()->create([$foreignKey => $current->id, 'game_version_id' => $default->id]);

    $outdated = $model::factory()->create();
    $dataModel::factory()->create([$foreignKey => $outdated->id, 'game_version_id' => $other->id]);

    $neverImported = $model::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => $segment])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-'.$segment.'.xml'));

    expect($content)->toContain(route($route, $current->slug ?? $current->uuid));
    expect($content)->not->toContain(route($route, $outdated->slug ?? $outdated->uuid));
    expect($content)->not->toContain(route($route, $neverImported->slug ?? $neverImported->uuid));
})->with([
    'vehicles' => ['vehicles', 'web.vehicles.show', Vehicle::class, VehicleData::class, 'vehicle_id'],
    'items' => ['items', 'web.items.show', Item::class, ItemData::class, 'item_id'],
    'blueprints' => ['blueprints', 'web.blueprints.show', Blueprint::class, BlueprintData::class, 'blueprint_id'],
    'missions' => ['missions', 'web.missions.show', Mission::class, MissionData::class, 'mission_id'],
    'locations' => ['locations', 'web.locations.show', StarmapLocation::class, StarmapLocationData::class, 'starmap_location_id'],
]);

it('excludes locations whose default-version data has no system', function (): void {
    $version = createDefaultGameVersion();

    $withSystem = StarmapLocation::factory()->create();
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $withSystem->id,
        'game_version_id' => $version->id,
        'system' => 'Stanton',
    ]);

    $systemless = StarmapLocation::factory()->create();
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $systemless->id,
        'game_version_id' => $version->id,
        'system' => null,
    ]);

    $this->artisan('sitemap:generate', ['--only' => 'locations'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-locations.xml'));

    expect($content)->toContain(route('web.locations.show', $withSystem->slug ?? $withSystem->uuid));
    expect($content)->not->toContain(route('web.locations.show', $systemless->slug ?? $systemless->uuid));
});

it('excludes vehicle items that redirect to the vehicles page', function (): void {
    $version = createDefaultGameVersion();

    $vehicleItem = Item::factory()->create();
    ItemData::factory()->create(['item_id' => $vehicleItem->id, 'game_version_id' => $version->id]);
    Vehicle::factory()->create(['uuid' => $vehicleItem->uuid]);

    $plainItem = Item::factory()->create();
    ItemData::factory()->create(['item_id' => $plainItem->id, 'game_version_id' => $version->id]);

    $this->artisan('sitemap:generate', ['--only' => 'items'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-items.xml'));

    expect($content)->not->toContain(route('web.items.show', $vehicleItem->slug));
    expect($content)->toContain(route('web.items.show', $plainItem->slug));
});

it('includes ship-matrix linked vehicles without default version data and their pages resolve', function (): void {
    createDefaultGameVersion();
    $other = GameVersion::factory()->create();

    $announced = Vehicle::factory()->create();
    $shipMatrixVehicle = ShipMatrixVehicle::factory()->create(['slug' => $announced->slug]);
    VehicleData::factory()->create([
        'vehicle_id' => $announced->id,
        'game_version_id' => $other->id,
        'shipmatrix_id' => $shipMatrixVehicle->id,
    ]);

    $this->artisan('sitemap:generate', ['--only' => 'vehicles'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-vehicles.xml'));
    $url = route('web.vehicles.show', $announced->slug);

    expect($content)->toContain($url);

    $this->get($url)->assertSuccessful();
});

it('excludes vehicles without default version data or a ship-matrix entry', function (): void {
    createDefaultGameVersion();
    $other = GameVersion::factory()->create();

    $unlinked = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $unlinked->id,
        'game_version_id' => $other->id,
        'shipmatrix_id' => null,
    ]);

    $bare = Vehicle::factory()->create();

    $this->artisan('sitemap:generate', ['--only' => 'vehicles'])
        ->assertExitCode(Command::SUCCESS);

    $content = file_get_contents(storage_path('app/sitemaps/sitemap-vehicles.xml'));

    expect($content)->not->toContain(route('web.vehicles.show', $unlinked->slug));
    expect($content)->not->toContain(route('web.vehicles.show', $bare->slug));
});

it('skips game data segments when no default game version exists', function (): void {
    Item::factory()->create();

    File::ensureDirectoryExists(storage_path('app/sitemaps'));
    File::put(storage_path('app/sitemaps/sitemap-items.xml'), '<urlset/>');

    $this->artisan('sitemap:generate', ['--only' => 'items'])
        ->expectsOutputToContain('No default game version')
        ->assertExitCode(Command::SUCCESS);

    expect(storage_path('app/sitemaps/sitemap-items.xml'))->not->toBeFile();

    $index = file_get_contents(storage_path('app/sitemaps/sitemap.xml'));

    expect($index)->not->toContain('sitemap-items.xml');
});

it('removes sitemap files of retired segments', function (): void {
    File::ensureDirectoryExists(storage_path('app/sitemaps'));
    File::put(storage_path('app/sitemaps/sitemap-starsystems.xml'), '<urlset/>');

    $this->artisan('sitemap:generate', ['--only' => 'static'])
        ->assertExitCode(Command::SUCCESS);

    expect(storage_path('app/sitemaps/sitemap-starsystems.xml'))->not->toBeFile();
    expect(storage_path('app/sitemaps/sitemap-static.xml'))->toBeFile();
});

it('sets priority 1.0 for game data segments', function (): void {
    $version = createDefaultGameVersion();
    $item = Item::factory()->create();
    ItemData::factory()->create(['item_id' => $item->id, 'game_version_id' => $version->id]);

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

describe('starmap show URLs', function (): void {
    it('are excluded because their web pages return 204 No Content', function (): void {
        Channel::factory()->create(['name' => 'General']);
        $commLink = CommLink::factory()->create();
        $system = Starsystem::factory()->create();
        $object = CelestialObject::factory()->create();

        $this->artisan('sitemap:generate')
            ->assertExitCode(Command::SUCCESS);

        foreach (glob(storage_path('app/sitemaps/sitemap*.xml')) as $file) {
            $content = file_get_contents($file);

            expect($content)->not->toContain(route('web.starmap.systems.show', $system->code));
            expect($content)->not->toContain(route('web.starmap.celestial-objects.show', $object->code));
        }

        // Index pages render real views and stay in the static segment.
        $staticContent = file_get_contents(storage_path('app/sitemaps/sitemap-static.xml'));

        expect($staticContent)->toContain(route('web.starmap.systems.index'));
        expect($staticContent)->toContain(route('web.starmap.celestial-objects.index'));

        // Other segments are still generated.
        $commLinksContent = file_get_contents(storage_path('app/sitemaps/sitemap-comm-links.xml'));

        expect($commLinksContent)->toContain(route('web.comm-links.show', $commLink->cig_id));
    });

    it('fail as invalid segments when requested via --only', function (string $segment): void {
        $this->artisan('sitemap:generate', ['--only' => $segment])
            ->assertExitCode(Command::FAILURE);
    })->with(['starsystems', 'celestial-objects']);
});
