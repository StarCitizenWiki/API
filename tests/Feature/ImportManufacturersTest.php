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

    $m1 = fake()->uuid();
    $m2 = fake()->uuid();
    $m3 = fake()->uuid();
    $m4 = fake()->uuid();

    $payload = [
        ['Reference' => $m1, 'Name' => 'Alpha', 'Code' => 'ALP'],
        ['Reference' => $m2, 'Name' => 'Beta', 'Code' => 'BET'],
        ['Reference' => $m3, 'Name' => 'Gamma'],
        ['Reference' => $m4, 'Code' => 'NON'],
    ];

    Storage::disk('scunpacked')->put('manufacturers.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-manufacturers')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 3 manufacturers (3 new, 0 updated). Skipped 1 invalid.');

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => $m1,
        'name' => 'Alpha',
        'code' => 'ALP',
    ]);

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => $m2,
        'name' => 'Beta',
        'code' => 'BET',
    ]);

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => $m3,
        'name' => 'Gamma',
        'code' => '',
    ]);
});

it('upserts existing manufacturers and reports counts', function (): void {
    Storage::fake('scunpacked');

    $existing = fake()->uuid();
    $new = fake()->uuid();

    Manufacturer::query()->create([
        'uuid' => $existing,
        'name' => 'Existing Name',
        'code' => 'OLD',
    ]);

    $payload = [
        ['Reference' => $existing, 'Name' => 'Updated Name', 'Code' => 'NEW'],
        ['Reference' => $new, 'Name' => 'New Manufacturer', 'Code' => 'NEWC'],
    ];

    Storage::disk('scunpacked')->put('manufacturers.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $this->artisan('game:import-manufacturers')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Imported 2 manufacturers (1 new, 1 updated). Skipped 0 invalid.');

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => $existing,
        'name' => 'Updated Name',
        'code' => 'NEW',
    ]);

    $this->assertDatabaseHas('game_manufacturers', [
        'uuid' => $new,
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
