<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;

if (! function_exists('createDefaultGameVersion')) {
    /**
     * Standard default game version used by Mission/Resource tests.
     * Centralized so test files stop repeating the same factory config.
     */
    function createDefaultGameVersion(): GameVersion
    {
        return GameVersion::factory()->create([
            'code' => '4.0.0-LIVE',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
        ]);
    }
}
