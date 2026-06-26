<?php

declare(strict_types=1);

use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->gameVersion = createDefaultGameVersion();
});

it('returns aggregated_spawns grouped by role, group_name, and spawn_kind', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'has_combat' => true,
            'data' => [
                'CombatSummary' => [
                    'Total' => ['Min' => 3, 'Max' => 8],
                ],
                'Combat' => [
                    ['Role' => 'enemy', 'Weight' => 1, 'GroupName' => 'guards', 'SpawnKind' => 'Npc', 'ConcurrentAmount' => 2],
                    ['Role' => 'enemy', 'Weight' => 1, 'GroupName' => 'guards', 'SpawnKind' => 'Npc', 'ConcurrentAmount' => 4],
                    ['Role' => 'enemy', 'Weight' => 2, 'GroupName' => 'snipers', 'SpawnKind' => 'Npc', 'ConcurrentAmount' => 1],
                    ['Role' => 'defend_target', 'Weight' => 1, 'GroupName' => 'vip', 'SpawnKind' => 'Npc', 'ConcurrentAmount' => 1],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful();

    $aggregated = $response->json('data.combat.aggregated_spawns');

    expect($aggregated)->toHaveCount(3);

    // Role grouping is the contract; within-role order is incidental.
    $byGroup = collect($aggregated)->keyBy('group_name');
    expect($byGroup->get('guards'))->toBe([
        'role' => 'enemy',
        'group_name' => 'guards',
        'spawn_kind' => 'Npc',
        'concurrent_min' => 2,
        'concurrent_max' => 4,
        'weight' => 1,
        'ships' => [],
    ])
        ->and($byGroup->get('snipers'))->toBe([
            'role' => 'enemy',
            'group_name' => 'snipers',
            'spawn_kind' => 'Npc',
            'concurrent_min' => 1,
            'concurrent_max' => 1,
            'weight' => 2,
            'ships' => [],
        ])
        ->and($byGroup->get('vip'))->toBe([
            'role' => 'defend_target',
            'group_name' => 'vip',
            'spawn_kind' => 'Npc',
            'concurrent_min' => 1,
            'concurrent_max' => 1,
            'weight' => 1,
            'ships' => [],
        ]);
});

it('sorts aggregated_spawns by role priority', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'has_combat' => true,
            'data' => [
                'CombatSummary' => ['Total' => ['Min' => 1, 'Max' => 1]],
                'Combat' => [
                    ['Role' => 'escort_target', 'Weight' => 1, 'GroupName' => 'cargo', 'SpawnKind' => 'Npc', 'ConcurrentAmount' => 1],
                    ['Role' => 'enemy', 'Weight' => 1, 'GroupName' => 'hostiles', 'SpawnKind' => 'Ship', 'ConcurrentAmount' => 3],
                    ['Role' => 'defend_target', 'Weight' => 1, 'GroupName' => 'station', 'SpawnKind' => 'Npc', 'ConcurrentAmount' => 2],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");
    $roles = collect($response->json('data.combat.aggregated_spawns'))->pluck('role')->all();

    expect($roles)->toBe(['enemy', 'defend_target', 'escort_target']);
});

it('normalizes unknown roles to other', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'has_combat' => true,
            'data' => [
                'CombatSummary' => ['Total' => ['Min' => 1, 'Max' => 1]],
                'Combat' => [
                    ['Role' => 'unknown_role', 'Weight' => 1, 'GroupName' => 'mystery', 'SpawnKind' => 'Npc', 'ConcurrentAmount' => 1],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");
    $aggregated = $response->json('data.combat.aggregated_spawns');

    expect($aggregated)->toHaveCount(1);
    expect($aggregated[0]['role'])->toBe('other');
});

it('returns merged_tags combining tags and markup_tags in entity_spawns', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'data' => [
                'CombatSummary' => ['Total' => ['Min' => 1, 'Max' => 1]],
                'EntitySpawns' => [
                    [
                        'Tags' => [['Name' => 'hostile'], ['Name' => 'armored']],
                        'Amount' => 3,
                        'Weight' => 1,
                        'GroupName' => 'wave_1',
                        'MarkupTags' => [['Name' => 'armored'], ['Name' => 'elite']],
                        'NegativeTags' => null,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");
    $spawns = $response->json('data.entity_spawns');

    expect($spawns)->toHaveCount(1);
    expect($spawns[0]['merged_tags'])->toContain('hostile', 'armored', 'elite');
    expect($spawns[0]['merged_tags'])->toHaveCount(3);
});

it('returns null combat when no combat data exists', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create();

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful();
    expect($response->json('data.combat'))->toBeNull();
});

it('surfaces resolved ships per spawn option and deduped per aggregated wave', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'has_combat' => true,
            'data' => [
                'CombatSummary' => ['Total' => ['Min' => 2, 'Max' => 4]],
                'Combat' => [
                    // Two ship-pool options in the same wave group; the Avenger is shared.
                    ['Role' => 'enemy', 'Weight' => 1, 'GroupName' => 'wave1', 'SpawnKind' => 'Ship', 'ConcurrentAmount' => 2,
                        'Ships' => [
                            ['ClassName' => 'AEGS_Avenger_Stalker', 'Name' => 'Aegis Avenger Stalker'],
                            ['ClassName' => 'ANVL_F7C_Hornet', 'Name' => 'Anvil F7C Hornet Mk I'],
                        ]],
                    ['Role' => 'enemy', 'Weight' => 1, 'GroupName' => 'wave1', 'SpawnKind' => 'Ship', 'ConcurrentAmount' => 4,
                        'Ships' => [
                            ['ClassName' => 'AEGS_Avenger_Stalker', 'Name' => 'Aegis Avenger Stalker'],
                            ['ClassName' => 'DRAK_Cutlass_Black', 'Name' => 'Drake Cutlass Black'],
                        ]],
                    // An NPC row in the same group contributes no ships.
                    ['Role' => 'enemy', 'Weight' => 1, 'GroupName' => 'wave1', 'SpawnKind' => 'Npc', 'ConcurrentAmount' => 2],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");
    $response->assertSuccessful();

    // Per option: each Ship row carries its own pool's ships verbatim.
    $shipSpawns = collect($response->json('data.combat.spawns'))
        ->where('spawn_kind', 'Ship')->values()->all();
    expect($shipSpawns)->toHaveCount(2)
        ->and($shipSpawns[0]['ships'])->toBe([
            ['class_name' => 'AEGS_Avenger_Stalker', 'name' => 'Aegis Avenger Stalker'],
            ['class_name' => 'ANVL_F7C_Hornet', 'name' => 'Anvil F7C Hornet Mk I'],
        ]);

    // Aggregated: deduped union across the wave's options (shared Avenger collapses), sorted by name.
    $agg = collect($response->json('data.combat.aggregated_spawns'))->firstWhere('group_name', 'wave1');
    expect($agg['ships'])->toBe([
        ['class_name' => 'AEGS_Avenger_Stalker', 'name' => 'Aegis Avenger Stalker'],
        ['class_name' => 'ANVL_F7C_Hornet', 'name' => 'Anvil F7C Hornet Mk I'],
        ['class_name' => 'DRAK_Cutlass_Black', 'name' => 'Drake Cutlass Black'],
    ]);
});

it('dedupes ships by class_name and falls back to name when class_name is absent', function (): void {
    // Alien/NPC-only hulls carry no ClassName; they must still surface (display-only, not linkable)
    // and dedupe by name.
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'has_combat' => true,
            'data' => [
                'CombatSummary' => ['Total' => ['Min' => 1, 'Max' => 1]],
                'Combat' => [
                    ['Role' => 'enemy', 'Weight' => 1, 'GroupName' => 'aliens', 'SpawnKind' => 'Ship', 'ConcurrentAmount' => 1,
                        'Ships' => [
                            ['Name' => 'Vanduul Mauler Destroyer'],
                            ['Name' => 'Vanduul Mauler Destroyer'],
                            ['ClassName' => 'VNCL_Blade', 'Name' => 'Esperia Blade'],
                        ]],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $ships = collect($response->json('data.combat.aggregated_spawns'))
        ->firstWhere('group_name', 'aliens')['ships'];

    expect($ships)->toBe([
        ['class_name' => 'VNCL_Blade', 'name' => 'Esperia Blade'],
        ['class_name' => null, 'name' => 'Vanduul Mauler Destroyer'],
    ]);
});
