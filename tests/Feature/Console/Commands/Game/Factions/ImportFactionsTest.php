<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\FactionReputationRef;
use App\Models\Game\FactionScope;
use App\Models\Game\FactionStanding;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('scunpacked');
});

it('imports a simple faction', function (): void {
    Storage::disk('scunpacked')->put('factions/faction_lawful_civilian.json', json_encode([
        'UUID' => 'dfb4d61e-65c1-4185-a204-e9583052d503',
        'Name' => 'Civilian',
        'Description' => 'A member of the general population.',
        'DefaultReaction' => 'Neutral',
        'FactionType' => 'Lawful',
        'AbleToArrest' => false,
        'PolicesLawfulTrespass' => false,
        'PolicesCriminality' => false,
        'NoLegalRights' => false,
    ]));

    $this->artisan('game:import-factions')
        ->assertSuccessful()
        ->expectsOutputToContain('Imported 1 factions. Skipped 0.');

    $faction = Faction::query()->where('uuid', 'dfb4d61e-65c1-4185-a204-e9583052d503')->first();

    expect($faction)->not->toBeNull()
        ->and($faction->name)->toBe('Civilian')
        ->and($faction->description)->toBe('A member of the general population.')
        ->and($faction->default_reaction)->toBe('Neutral')
        ->and($faction->faction_type)->toBe('Lawful')
        ->and($faction->has_reputation)->toBeFalse()
        ->and($faction->able_to_arrest)->toBeFalse();
});

it('imports a reputation faction with scopes, standings, and refs', function (): void {
    Storage::disk('scunpacked')->put('factions/faction_reputation_lawful_test.json', json_encode([
        'UUID' => '29e65591-d776-49a4-9a98-eb1fd7c51fcc',
        'Name' => '<= UNINITIALIZED =>',
        'DefaultReaction' => 'Neutral',
        'FactionType' => 'Lawful',
        'AbleToArrest' => false,
        'PolicesLawfulTrespass' => false,
        'PolicesCriminality' => false,
        'NoLegalRights' => false,
        'Reputation' => [
            'UUID' => '6fe6526a-4ae0-44ec-a5b7-4a1a7f8bdc97',
            'DisplayName' => 'Test Guild',
            'IsNpc' => false,
            'HideInDelphiApp' => true,
            'Context' => [
                'SortOrderScope' => 'Alphabetical',
                'PrimaryScope' => [
                    'UUID' => 'f340b011-c40c-4229-b9c1-ce6a3eb1df77',
                    'ScopeName' => 'Affinity',
                    'DisplayName' => 'Affinity',
                    'ReputationCeiling' => 10000,
                    'InitialReputation' => 0,
                    'Standings' => [
                        [
                            'UUID' => 'bcd1f776-9a6e-4ffd-85c4-6df64f4cf2ed',
                            'Name' => 'Affinity_Good_100_Exalted',
                            'DisplayName' => 'Exalted',
                            'MinReputation' => 10000,
                            'DriftReputation' => 0,
                            'DriftTimeHours' => 0,
                            'Gated' => false,
                        ],
                        [
                            'UUID' => 'cf6f6a08-b8a0-4b13-9e20-2b74cd783059',
                            'Name' => 'Affinity_000_Neutral',
                            'DisplayName' => 'Neutral',
                            'MinReputation' => 0,
                            'DriftReputation' => 0,
                            'DriftTimeHours' => 0,
                            'Gated' => true,
                        ],
                    ],
                ],
            ],
            'Hostility' => [
                'ScopeUUID' => 'd31bd373-35ab-4ea4-b0a7-0ccc867ef082',
                'StandingUUID' => 'af6fff58-f725-4739-bdfa-256e98d19d18',
                'Scope' => [
                    'UUID' => 'd31bd373-35ab-4ea4-b0a7-0ccc867ef082',
                    'ScopeName' => 'FactionReputation',
                    'DisplayName' => 'Standing',
                    'ReputationCeiling' => 95250,
                    'InitialReputation' => 0,
                    'Standings' => [
                        [
                            'UUID' => 'a8f635c5-7fc0-4d27-8a08-27bb3476bfb8',
                            'Name' => 'FactionRep_Neutral_Rank0',
                            'DisplayName' => 'Neutral',
                            'MinReputation' => 0,
                            'DriftReputation' => 0,
                            'DriftTimeHours' => 0,
                            'Gated' => true,
                        ],
                    ],
                ],
                'Standing' => [
                    'UUID' => 'af6fff58-f725-4739-bdfa-256e98d19d18',
                    'Name' => 'FactionRep_Negative',
                    'DisplayName' => 'Hostile',
                    'MinReputation' => -1,
                    'DriftReputation' => 0,
                    'DriftTimeHours' => 0,
                    'Gated' => false,
                ],
            ],
            'Allied' => [
                'ScopeUUID' => 'd31bd373-35ab-4ea4-b0a7-0ccc867ef082',
                'StandingUUID' => 'a8f635c5-7fc0-4d27-8a08-27bb3476bfb8',
            ],
            'Properties' => [
                'Description' => 'A test guild.',
                'Lawful' => true,
                'Headquarters' => 'Test HQ',
                'Founded' => '2387',
                'Leadership' => 'Test Leader',
                'Area' => 'UEE',
                'Focus' => 'Testing',
            ],
        ],
    ]));

    $this->artisan('game:import-factions')
        ->assertSuccessful()
        ->expectsOutputToContain('Imported 1 factions. Skipped 0.');

    $faction = Faction::query()->where('uuid', '29e65591-d776-49a4-9a98-eb1fd7c51fcc')->first();

    expect($faction)->not->toBeNull()
        ->and($faction->name)->toBe('Test Guild')
        ->and($faction->has_reputation)->toBeTrue()
        ->and($faction->headquarters)->toBe('Test HQ')
        ->and($faction->founded)->toBe('2387')
        ->and($faction->lawful)->toBeTrue()
        ->and($faction->hide_in_delphi_app)->toBeTrue()
        ->and($faction->sort_order_scope)->toBe('Alphabetical');

    expect(FactionScope::query()->count())->toBe(2);
    expect(FactionStanding::query()->count())->toBe(4); // 2 affinity + 1 factionrep scope + 1 hostility standing

    $affinityScope = FactionScope::query()->where('uuid', 'f340b011-c40c-4229-b9c1-ce6a3eb1df77')->first();
    expect($affinityScope)->not->toBeNull()
        ->and($affinityScope->scope_name)->toBe('Affinity')
        ->and($affinityScope->standings)->toHaveCount(2);

    $hostileStanding = FactionStanding::query()->where('uuid', 'af6fff58-f725-4739-bdfa-256e98d19d18')->first();
    expect($hostileStanding)->not->toBeNull()
        ->and($hostileStanding->name)->toBe('FactionRep_Negative');

    $repRef = FactionReputationRef::query()->where('faction_id', $faction->id)->first();
    $factionRepScope = FactionScope::query()->where('uuid', 'd31bd373-35ab-4ea4-b0a7-0ccc867ef082')->first();
    expect($repRef)->not->toBeNull()
        ->and($repRef->faction_scope_id)->toBe($factionRepScope->id);
});

it('skips uninitialized factions without display name', function (): void {
    Storage::disk('scunpacked')->put('factions/faction_template.json', json_encode([
        'UUID' => '00000000-0000-0000-0000-000000000001',
        'Name' => '<= UNINITIALIZED =>',
        'DefaultReaction' => 'Neutral',
        'FactionType' => 'Lawful',
        'AbleToArrest' => false,
        'PolicesLawfulTrespass' => false,
        'PolicesCriminality' => false,
        'NoLegalRights' => false,
    ]));

    $this->artisan('game:import-factions')
        ->assertSuccessful()
        ->expectsOutputToContain('Imported 0 factions. Skipped 1.');

    expect(Faction::query()->count())->toBe(0);
});

it('is idempotent when run twice', function (): void {
    $simpleFaction = [
        'UUID' => 'dfb4d61e-65c1-4185-a204-e9583052d503',
        'Name' => 'Civilian',
        'Description' => 'A member of the general population.',
        'DefaultReaction' => 'Neutral',
        'FactionType' => 'Lawful',
        'AbleToArrest' => false,
        'PolicesLawfulTrespass' => false,
        'PolicesCriminality' => false,
        'NoLegalRights' => false,
    ];

    Storage::disk('scunpacked')->put('factions/faction_lawful_civilian.json', json_encode($simpleFaction));

    $this->artisan('game:import-factions')->assertSuccessful();
    $this->artisan('game:import-factions')->assertSuccessful();

    expect(Faction::query()->count())->toBe(1);
});

it('imports a mixed batch of simple and reputation factions', function (): void {
    Storage::disk('scunpacked')->put('factions/faction_lawful_civilian.json', json_encode([
        'UUID' => 'dfb4d61e-65c1-4185-a204-e9583052d503',
        'Name' => 'Civilian',
        'DefaultReaction' => 'Neutral',
        'FactionType' => 'Lawful',
        'AbleToArrest' => false,
        'PolicesLawfulTrespass' => false,
        'PolicesCriminality' => false,
        'NoLegalRights' => false,
    ]));

    Storage::disk('scunpacked')->put('factions/faction_reputation_lawful_test.json', json_encode([
        'UUID' => '29e65591-d776-49a4-9a98-eb1fd7c51fcc',
        'Name' => '<= UNINITIALIZED =>',
        'DefaultReaction' => 'Neutral',
        'FactionType' => 'Lawful',
        'AbleToArrest' => false,
        'PolicesLawfulTrespass' => false,
        'PolicesCriminality' => false,
        'NoLegalRights' => false,
        'Reputation' => [
            'UUID' => '6fe6526a-4ae0-44ec-a5b7-4a1a7f8bdc97',
            'DisplayName' => 'Test Guild',
            'IsNpc' => false,
            'HideInDelphiApp' => false,
            'Context' => [
                'SortOrderScope' => 'Alphabetical',
                'PrimaryScope' => [
                    'UUID' => 'f340b011-c40c-4229-b9c1-ce6a3eb1df77',
                    'ScopeName' => 'Affinity',
                    'DisplayName' => 'Affinity',
                    'ReputationCeiling' => 10000,
                    'InitialReputation' => 0,
                    'Standings' => [
                        [
                            'UUID' => 'cf6f6a08-b8a0-4b13-9e20-2b74cd783059',
                            'Name' => 'Affinity_000_Neutral',
                            'DisplayName' => 'Neutral',
                            'MinReputation' => 0,
                            'DriftReputation' => 0,
                            'DriftTimeHours' => 0,
                            'Gated' => true,
                        ],
                    ],
                ],
            ],
            'Hostility' => [
                'ScopeUUID' => 'd31bd373-35ab-4ea4-b0a7-0ccc867ef082',
                'StandingUUID' => 'af6fff58-f725-4739-bdfa-256e98d19d18',
                'Scope' => [
                    'UUID' => 'd31bd373-35ab-4ea4-b0a7-0ccc867ef082',
                    'ScopeName' => 'FactionReputation',
                    'DisplayName' => 'Standing',
                    'ReputationCeiling' => 95250,
                    'InitialReputation' => 0,
                    'Standings' => [
                        [
                            'UUID' => 'a8f635c5-7fc0-4d27-8a08-27bb3476bfb8',
                            'Name' => 'FactionRep_Neutral_Rank0',
                            'DisplayName' => 'Neutral',
                            'MinReputation' => 0,
                            'DriftReputation' => 0,
                            'DriftTimeHours' => 0,
                            'Gated' => true,
                        ],
                    ],
                ],
                'Standing' => [
                    'UUID' => 'af6fff58-f725-4739-bdfa-256e98d19d18',
                    'Name' => 'FactionRep_Negative',
                    'DisplayName' => 'Hostile',
                    'MinReputation' => -1,
                    'DriftReputation' => 0,
                    'DriftTimeHours' => 0,
                    'Gated' => false,
                ],
            ],
            'Allied' => [
                'ScopeUUID' => 'd31bd373-35ab-4ea4-b0a7-0ccc867ef082',
                'StandingUUID' => 'a8f635c5-7fc0-4d27-8a08-27bb3476bfb8',
            ],
            'Properties' => [
                'Lawful' => true,
            ],
        ],
    ]));

    Storage::disk('scunpacked')->put('factions/faction_template.json', json_encode([
        'UUID' => '00000000-0000-0000-0000-000000000001',
        'Name' => '<= UNINITIALIZED =>',
        'DefaultReaction' => 'Neutral',
        'FactionType' => 'Lawful',
        'AbleToArrest' => false,
        'PolicesLawfulTrespass' => false,
        'PolicesCriminality' => false,
        'NoLegalRights' => false,
    ]));

    $this->artisan('game:import-factions')
        ->assertSuccessful()
        ->expectsOutputToContain('Imported 2 factions. Skipped 1.');

    expect(Faction::query()->count())->toBe(2);
    expect(FactionReputationRef::query()->count())->toBe(1);
});

it('warns when no faction files are found', function (): void {
    $this->artisan('game:import-factions')
        ->assertSuccessful()
        ->expectsOutputToContain('No faction files found');
});
