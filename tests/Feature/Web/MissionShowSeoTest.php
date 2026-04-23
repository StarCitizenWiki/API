<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('renders seo meta tags on mission show page', function (): void {
    $faction = Faction::factory()->create([
        'name' => 'Nine Tails',
        'faction_type' => 'Unlawful',
        'lawful' => false,
    ]);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'faction_id' => $faction->id,
            'title' => 'Nine Tails Heist',
            'mission_type' => 'Delivery',
            'description' => 'Deliver cargo through dangerous space.',
        ]);

    $response = $this->get("/missions/{$mission->slug}");

    $response->assertSuccessful()
        ->assertSee('Nine Tails Heist | Delivery | Star Citizen Mission', false)
        ->assertSee('Deliver cargo through dangerous space.', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('og:title', false)
        ->assertSee('og:description', false)
        ->assertSee('twitter:card', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('BreadcrumbList', false);
});

it('resolves mission show by UUID for backward compatibility', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['title' => 'UUID Mission']);

    $response = $this->get("/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertSee('UUID Mission');
});

it('renders canonical url with slug on mission show page', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['title' => 'Test Mission']);

    $response = $this->get("/missions/{$mission->slug}?version=4.0.0-LIVE");

    $response->assertSuccessful()
        ->assertSee('rel="canonical"', false)
        ->assertSee('/missions/'.$mission->slug, false);
});

it('renders seo breadcrumbs with faction on mission show page', function (): void {
    $faction = Faction::factory()->create([
        'name' => 'Nine Tails',
        'faction_type' => 'Unlawful',
        'lawful' => false,
    ]);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'faction_id' => $faction->id,
            'title' => 'Heist Mission',
        ]);

    $response = $this->get("/missions/{$mission->slug}");

    $response->assertSuccessful()
        ->assertSee('Nine Tails</a>', false)
        ->assertSee('All Missions</a>', false);
});

it('renders seo breadcrumbs without faction when mission has no faction', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['faction_id' => null, 'title' => 'Solo Mission']);

    $response = $this->get("/missions/{$mission->slug}");

    $response->assertSuccessful()
        ->assertSee('All Missions</a>', false)
        ->assertSee('Solo Mission', false);
});
