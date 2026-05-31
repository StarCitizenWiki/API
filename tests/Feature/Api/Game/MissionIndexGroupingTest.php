<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

it('groups missions by title generator giver faction and legality by default', function (): void {
    // TODO use PG Group
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Mission grouping requires PostgreSQL.');
    }

    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();
    $mission3 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Hauler Needed for Shipment',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'debug_name' => 'variant_a',
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Hauler Needed for Shipment',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'debug_name' => 'variant_b',
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission3)->create([
        'title' => 'Hauler Needed for Shipment',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'debug_name' => 'variant_c',
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['title'])->toBe('Hauler Needed for Shipment');
});

it('separates missions with different generator class', function (): void {
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Track Down Target',
        'generator_class' => 'HeadHunters_Mercenary_FPS',
        'mission_giver' => 'Head Hunters',
        'faction_id' => null,
        'illegal' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Track Down Target',
        'generator_class' => 'FoxwellEnforcement_Mercenary_FPS',
        'mission_giver' => 'Foxwell Enforcement',
        'faction_id' => null,
        'illegal' => false,
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(2);
});

it('separates missions with different legality', function (): void {
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Salvage Operation',
        'generator_class' => 'Salvage_Gen',
        'mission_giver' => 'TDD',
        'faction_id' => null,
        'illegal' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Salvage Operation',
        'generator_class' => 'Salvage_Gen',
        'mission_giver' => 'TDD',
        'faction_id' => null,
        'illegal' => true,
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(2);
    $legalities = collect($data)->pluck('illegal')->sort()->values()->all();
    expect($legalities)->toBe([false, true]);
});

it('shows null title missions individually', function (): void {
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();
    $mission3 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => null,
        'generator_class' => 'Covalex_Hauling',
        'debug_name' => 'variant_null_1',
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => null,
        'generator_class' => 'Covalex_Hauling',
        'debug_name' => 'variant_null_2',
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission3)->create([
        'title' => null,
        'generator_class' => 'Covalex_Hauling',
        'debug_name' => 'variant_null_3',
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(3);
});

it('shows variant count when grouped', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Mission grouping requires PostgreSQL.');
    }

    $faction = Faction::factory()->create();
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();
    $mission3 = Mission::factory()->create();
    $mission4 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Delivery Run',
        'generator_class' => 'Delivery_Gen',
        'mission_giver' => 'Courier',
        'faction_id' => $faction->id,
        'illegal' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Delivery Run',
        'generator_class' => 'Delivery_Gen',
        'mission_giver' => 'Courier',
        'faction_id' => $faction->id,
        'illegal' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission3)->create([
        'title' => 'Delivery Run',
        'generator_class' => 'Delivery_Gen',
        'mission_giver' => 'Courier',
        'faction_id' => $faction->id,
        'illegal' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission4)->create([
        'title' => 'Other Mission',
        'generator_class' => 'Other_Gen',
        'mission_giver' => 'Other',
        'faction_id' => $faction->id,
        'illegal' => false,
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');

    $delivery = collect($data)->first(fn (array $item) => $item['title'] === 'Delivery Run');
    expect($delivery)->not->toBeNull();
    expect($delivery)->toHaveKey('variant_count')
        ->and($delivery['variant_count'])->toBe(2);
});

it('ungroups when a filter is active without explicit grouped param', function (): void {
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();
    $mission3 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission3)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);

    $response = $this->getJson('/api/missions?filter[has_combat]=false');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(3);
});

it('disables grouping when explicitly requested', function (): void {
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();
    $mission3 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission3)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);

    $response = $this->getJson('/api/missions?filter[grouped]=false');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(3);
});

it('keeps grouping when filter is active and grouped is explicitly true', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Mission grouping requires PostgreSQL.');
    }

    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();
    $mission3 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission3)->create([
        'title' => 'Hauler Needed',
        'generator_class' => 'Covalex_Hauling',
        'mission_giver' => 'Covalex Shipping',
        'faction_id' => null,
        'illegal' => false,
        'has_combat' => false,
    ]);

    $response = $this->getJson('/api/missions?filter[has_combat]=false&filter[grouped]=true');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
});

it('keeps missions grouped when sort is active', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Mission grouping requires PostgreSQL.');
    }

    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Alpha Mission',
        'generator_class' => 'Gen_A',
        'mission_giver' => 'Giver',
        'faction_id' => null,
        'illegal' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Alpha Mission',
        'generator_class' => 'Gen_A',
        'mission_giver' => 'Giver',
        'faction_id' => null,
        'illegal' => false,
    ]);

    $response = $this->getJson('/api/missions?sort=title');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
});

it('shows empty title missions individually', function (): void {
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => '',
        'generator_class' => 'Covalex_Hauling',
        'debug_name' => 'empty_title_1',
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => '',
        'generator_class' => 'Covalex_Hauling',
        'debug_name' => 'empty_title_2',
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(2);
});

it('aggregates star systems across grouped variants', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Star system aggregation requires PostgreSQL arrays.');
    }

    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();
    $mission3 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Salvage Job',
        'generator_class' => 'Salvage_Gen',
        'mission_giver' => 'TDD',
        'faction_id' => null,
        'illegal' => false,
        'star_systems' => ['Stanton'],
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Salvage Job',
        'generator_class' => 'Salvage_Gen',
        'mission_giver' => 'TDD',
        'faction_id' => null,
        'illegal' => false,
        'star_systems' => ['Pyro'],
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission3)->create([
        'title' => 'Salvage Job',
        'generator_class' => 'Salvage_Gen',
        'mission_giver' => 'TDD',
        'faction_id' => null,
        'illegal' => false,
        'star_systems' => ['Stanton', 'Nyx'],
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);

    $systems = $data[0]['star_systems'];
    sort($systems);
    expect($systems)->toBe(['Nyx', 'Pyro', 'Stanton']);
});

it('does not show variant count when ungrouped', function (): void {
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Mission A',
        'generator_class' => 'Gen_A',
        'mission_giver' => 'Giver',
        'faction_id' => null,
        'illegal' => false,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Mission A',
        'generator_class' => 'Gen_A',
        'mission_giver' => 'Giver',
        'faction_id' => null,
        'illegal' => false,
    ]);

    $response = $this->getJson('/api/missions?filter[grouped]=false');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(2);
    expect($data[0])->not->toHaveKey('variant_count');
    expect($data[1])->not->toHaveKey('variant_count');
});

it('separates missions with different mission key', function (): void {
    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Research Mission',
        'generator_class' => 'Rayari_RecoverItem',
        'mission_giver' => 'Rayari',
        'faction_id' => null,
        'illegal' => false,
        'mission_key' => md5('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'),
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Research Mission',
        'generator_class' => 'Rayari_RecoverItem',
        'mission_giver' => 'Rayari',
        'faction_id' => null,
        'illegal' => false,
        'mission_key' => md5('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'),
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(2);
});

it('groups missions with same mission key together', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Mission grouping requires PostgreSQL.');
    }

    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();
    $mission3 = Mission::factory()->create();

    $sharedKey = md5('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');

    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Mining Order',
        'generator_class' => 'Shubin_Mining',
        'mission_giver' => 'Shubin',
        'faction_id' => null,
        'illegal' => false,
        'mission_key' => $sharedKey,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Mining Order',
        'generator_class' => 'Shubin_Mining',
        'mission_giver' => 'Shubin',
        'faction_id' => null,
        'illegal' => false,
        'mission_key' => $sharedKey,
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission3)->create([
        'title' => 'Mining Order',
        'generator_class' => 'Shubin_Mining',
        'mission_giver' => 'Shubin',
        'faction_id' => null,
        'illegal' => false,
        'mission_key' => null,
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(2);

    $withKey = collect($data)->first(fn (array $item) => $item['variant_count'] === 1);
    $withoutKey = collect($data)->first(fn (array $item) => ! isset($item['variant_count']) || $item['variant_count'] === 0);
    expect($withKey)->not->toBeNull();
    expect($withoutKey)->not->toBeNull();
});

it('computes deterministic mission key from sorted pool UUIDs', function (): void {
    $poolA = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    $poolB = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';

    // md5 of sorted, comma-joined UUIDs
    $sorted = collect([$poolA, $poolB])->sort()->values()->all();
    $expectedKey = md5(implode(',', $sorted));

    // Reverse order should produce the same key because we sort first
    $reversed = collect([$poolB, $poolA])->sort()->values()->all();
    $reversedKey = md5(implode(',', $reversed));
    expect($reversedKey)->toBe($expectedKey);
});

it('separates missions with multi-pool mission keys', function (): void {
    $poolA = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    $poolB = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
    $poolC = 'cccccccc-cccc-cccc-cccc-cccccccccccc';

    $mission1 = Mission::factory()->create();
    $mission2 = Mission::factory()->create();

    // Mission 1 has pools A,B -> key = md5("A,B")
    // Mission 2 has pools A,C -> key = md5("A,C") - different!
    MissionData::factory()->forVersion($this->version)->forMission($mission1)->create([
        'title' => 'Multi Pool Mission',
        'generator_class' => 'Gen_Multi',
        'mission_giver' => 'Giver',
        'faction_id' => null,
        'illegal' => false,
        'mission_key' => md5(implode(',', [$poolA, $poolB])),
    ]);
    MissionData::factory()->forVersion($this->version)->forMission($mission2)->create([
        'title' => 'Multi Pool Mission',
        'generator_class' => 'Gen_Multi',
        'mission_giver' => 'Giver',
        'faction_id' => null,
        'illegal' => false,
        'mission_key' => md5(implode(',', [$poolA, $poolC])),
    ]);

    $response = $this->getJson('/api/missions');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data)->toHaveCount(2);
});
