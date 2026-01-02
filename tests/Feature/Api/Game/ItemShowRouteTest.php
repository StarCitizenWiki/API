<?php

use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns not found instead of a server error when the item uuid is missing', function () {
    GameVersion::create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->getJson('/api/items/24bb4a97-3698-4860-aae5-a09dc21383a2');

    $response->assertNotFound();
});
