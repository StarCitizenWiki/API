<?php

declare(strict_types=1);

use App\Models\StarCitizen\Stat;

describe('GET /api/stats/latest', function (): void {
    it('returns the latest stat', function (): void {
        Stat::query()->create([
            'funds' => 962878547.56,
            'fans' => 6433852,
            'fleet' => 6433852,
        ]);

        $this->getJson('/api/stats/latest')
            ->assertOk()
            ->assertJsonPath('data.funds', '962878547.56')
            ->assertJsonPath('data.fans', 6433852)
            ->assertJsonPath('data.fleet', 6433852);
    });

    it('returns 404 when no stats exist', function (): void {
        $this->getJson('/api/stats/latest')->assertNotFound();
    });
});
