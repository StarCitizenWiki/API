<?php

declare(strict_types=1);

use App\Models\Game\Manufacturer;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('fails when the manufacturers file is missing', function (): void {
    Storage::fake('scunpacked');

    $this->artisan('game:import-manufacturers')
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('manufacturers.json not found in scunpacked storage.');
});

it('imports manufacturers and skips invalid rows', function (): void {
    Storage::fake('scunpacked');

    $payload = [
        ['reference' => 'uuid-alpha', 'name' => 'Alpha', 'code' => 'ALP'],
        ['reference' => 'uuid-beta', 'name' => 'Beta', 'code' => 'BET'],
        ['reference' => 'uuid-gamma', 'name' => 'Gamma'],
    ];

    Storage::disk('scunpacked')->put('manufacturers.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-manufacturers')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 manufacturers (2 new, 0 updated). Skipped 1 invalid.');

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => 'uuid-alpha',
        'name' => 'Alpha',
        'code' => 'ALP',
    ]);

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => 'uuid-beta',
        'name' => 'Beta',
        'code' => 'BET',
    ]);
});

it('upserts existing manufacturers and reports counts', function (): void {
    Storage::fake('scunpacked');

    Manufacturer::query()->create([
        'uuid' => 'uuid-existing',
        'name' => 'Existing Name',
        'code' => 'OLD',
    ]);

    $payload = [
        ['reference' => 'uuid-existing', 'name' => 'Updated Name', 'code' => 'NEW'],
        ['reference' => 'uuid-new', 'name' => 'New Manufacturer', 'code' => 'NEWC'],
    ];

    Storage::disk('scunpacked')->put('manufacturers.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-manufacturers')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 manufacturers (1 new, 1 updated). Skipped 0 invalid.');

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => 'uuid-existing',
        'name' => 'Updated Name',
        'code' => 'NEW',
    ]);

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => 'uuid-new',
        'name' => 'New Manufacturer',
        'code' => 'NEWC',
    ]);
});

it('fails when the manufacturers file contains invalid json', function (): void {
    Storage::fake('scunpacked');

    Storage::disk('scunpacked')->put('manufacturers.json', '{invalid');

    $this->artisan('game:import-manufacturers')
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Failed to decode manufacturers.json: Syntax error');
});
