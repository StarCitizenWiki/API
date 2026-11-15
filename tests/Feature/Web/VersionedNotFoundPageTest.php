<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

function createWebNotFoundVersions(): array
{
    $older = GameVersion::factory()->create([
        'code' => '4.10.2-LIVE.12881860',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subDay(),
    ]);

    $default = GameVersion::factory()->create([
        'code' => '4.10.3-LIVE.13165946',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    return ['older' => $older, 'default' => $default];
}

describe('versioned 404 pages', function (): void {
    it('shows the last existed hint and version link on vehicle pages', function (): void {
        $versions = createWebNotFoundVersions();
        $manufacturer = Manufacturer::factory()->create();

        $vehicle = Vehicle::factory()->create(['slug' => 'aegs-hammerhead']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($versions['older'], 'gameVersion')
            ->for($manufacturer)
            ->create();

        $response = $this->get(route('web.vehicles.show', $vehicle->slug));

        $response->assertStatus(404)
            ->assertSeeText('Not available in this version')
            ->assertSeeText('This entry has no data in 4.10.3.')
            ->assertSeeText('The most recent version with data is 4.10.2.')
            ->assertSeeText('View in 4.10.2')
            ->assertSee('version=4.10.2-LIVE.12881860');

        expect($response->headers->get('Cache-Control'))
            ->toContain('no-store')
            ->toContain('private');
    });

    it('replaces an explicit version query parameter in the link', function (): void {
        $versions = createWebNotFoundVersions();
        $missing = GameVersion::factory()->create([
            'code' => '4.9.0-LIVE.12232306',
            'channel' => 'live',
            'is_default' => false,
            'released_at' => now()->subDays(2),
        ]);
        $manufacturer = Manufacturer::factory()->create();

        $vehicle = Vehicle::factory()->create(['slug' => 'aegs-hammerhead']);
        VehicleData::factory()
            ->for($vehicle)
            ->for($versions['older'], 'gameVersion')
            ->for($manufacturer)
            ->create();

        $response = $this->get(route('web.vehicles.show', ['vehicle' => $vehicle->slug, 'version' => $missing->code]));

        $content = $response->getContent();

        // The nav keeps the selected version in its links, so target the hint anchor itself.
        expect(preg_match('/<a[^>]*href="[^"]*version=4\.10\.2-LIVE\.12881860"[^>]*>\s*View in 4\.10\.2\s*<\/a>/', $content))->toBe(1);

        $response->assertStatus(404)
            ->assertSeeText('This entry has no data in 4.9.0.');
    });

    it('shows the hint on item pages', function (): void {
        $versions = createWebNotFoundVersions();
        $manufacturer = Manufacturer::factory()->create();

        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($versions['older'], 'gameVersion')
            ->for($manufacturer)
            ->create([
                'class_name' => 'versioned_widget',
                'type' => 'Widget',
                'classification' => 'Test.Widget',
                'data' => ['stdItem' => []],
            ]);

        $this->get(route('web.items.show', $item->uuid))
            ->assertStatus(404)
            ->assertSeeText('The most recent version with data is 4.10.2.')
            ->assertSee('version=4.10.2-LIVE.12881860');
    });

    it('shows the hint on blueprint pages', function (): void {
        $versions = createWebNotFoundVersions();

        $blueprint = Blueprint::factory()->create();
        BlueprintData::factory()
            ->for($blueprint)
            ->for($versions['older'], 'gameVersion')
            ->create();

        $this->get(route('web.blueprints.show', $blueprint->uuid))
            ->assertStatus(404)
            ->assertSeeText('The most recent version with data is 4.10.2.')
            ->assertSee('version=4.10.2-LIVE.12881860');
    });
});

describe('generic 404 pages', function (): void {
    it('keeps the plain 404 for an unknown vehicle slug', function (): void {
        createWebNotFoundVersions();

        $this->get(route('web.vehicles.show', 'never-existed'))
            ->assertStatus(404)
            ->assertSeeText('Page not found')
            ->assertSeeText("We couldn't find this page. The link may be incorrect, or the page may no longer exist.")
            ->assertSeeText('Go to home')
            ->assertDontSeeText('The most recent version with data is');
    });

    it('keeps the plain 404 for a blueprint that never existed', function (): void {
        createWebNotFoundVersions();

        $this->get(route('web.blueprints.show', 'd46f4de1-8dc1-473b-9739-88c9ba7e75a6'))
            ->assertStatus(404)
            ->assertSeeText('Page not found')
            ->assertSeeText("We couldn't find this page. The link may be incorrect, or the page may no longer exist.")
            ->assertSeeText('Go to home')
            ->assertDontSeeText('The most recent version with data is');
    });
});
