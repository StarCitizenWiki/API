<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\Affiliation;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns starsystem filter values with counts', function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $affiliation = Affiliation::factory()->create(['name' => 'UEE']);

    $starsystem = Starsystem::factory()->create([
        'status' => 'ACTIVE',
        'type' => 'SYSTEM',
        'aggregated_size' => 42.5,
    ]);
    $starsystem->affiliation()->attach($affiliation);

    Starsystem::factory()->create([
        'status' => 'INACTIVE',
        'type' => 'SYSTEM',
        'aggregated_size' => 10.0,
    ]);

    $response = $this->getJson(route('starsystems.filters'));

    $response->assertSuccessful();

    $filters = $response->json('filters');

    expect(collect($filters['affiliation'])->contains(fn (array $row) => $row['value'] === 'UEE' && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['status'])->contains(fn (array $row) => $row['value'] === 'ACTIVE' && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['type'])->contains(fn (array $row) => $row['value'] === 'SYSTEM' && $row['count'] === 2))->toBeTrue()
        ->and(collect($filters['size'])->contains(fn (array $row) => $row['value'] === 42.5 && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['affiliation'])->contains(fn (array $row) => $row['value'] === null && $row['label'] === 'Unknown'))->toBeTrue();
});
