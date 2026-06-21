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
    ]);
    expect($byGroup->get('snipers'))->toBe([
        'role' => 'enemy',
        'group_name' => 'snipers',
        'spawn_kind' => 'Npc',
        'concurrent_min' => 1,
        'concurrent_max' => 1,
        'weight' => 2,
    ]);
    expect($byGroup->get('vip'))->toBe([
        'role' => 'defend_target',
        'group_name' => 'vip',
        'spawn_kind' => 'Npc',
        'concurrent_min' => 1,
        'concurrent_max' => 1,
        'weight' => 1,
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
