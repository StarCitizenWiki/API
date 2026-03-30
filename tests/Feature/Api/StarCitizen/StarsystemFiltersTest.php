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

    $this->getJson(route('starsystems.filters'))
        ->assertOk()
        ->assertExactJson([
            'filters' => [
                'affiliation' => [
                    ['value' => 'UEE', 'label' => 'UEE', 'count' => 1],
                    ['value' => null, 'label' => 'Unknown', 'count' => 1],
                ],
                'status' => [
                    ['value' => 'ACTIVE', 'label' => 'ACTIVE', 'count' => 1],
                    ['value' => 'INACTIVE', 'label' => 'INACTIVE', 'count' => 1],
                ],
                'type' => [
                    ['value' => 'SYSTEM', 'label' => 'SYSTEM', 'count' => 2],
                ],
                'size' => [
                    ['value' => 10, 'label' => '10', 'count' => 1],
                    ['value' => 42.5, 'label' => '42.5', 'count' => 1],
                ],
            ],
        ]);
});
