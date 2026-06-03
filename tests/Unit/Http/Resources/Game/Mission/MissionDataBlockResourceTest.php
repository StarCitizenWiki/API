<?php

declare(strict_types=1);

use App\Http\Resources\Game\Mission\MissionDataBlockResource;

describe('mapMissionTokens', function (): void {
    it('returns null for empty or invalid input', function (): void {
        expect(MissionDataBlockResource::mapMissionTokens([]))->toBeNull()
            ->and(MissionDataBlockResource::mapMissionTokens(null))->toBeNull()
            ->and(MissionDataBlockResource::mapMissionTokens('nope'))->toBeNull();
    });

    it('returns the full token map and preserves piped keys', function (): void {
        $tokens = MissionDataBlockResource::mapMissionTokens([
            'Location|Address' => ['Grim HEX'],
            'Danger' => ['Low'],
        ]);

        expect($tokens)->toBe([
            'Location|Address' => ['Grim HEX'],
            'Danger' => ['Low'],
        ]);
    });

    it('filters blank values', function (): void {
        $tokens = MissionDataBlockResource::mapMissionTokens([
            'Contractor' => ['', 'Valid'],
        ]);

        expect($tokens)->toBe(['Contractor' => ['Valid']]);
    });

    it('drops keys with no valid values', function (): void {
        expect(MissionDataBlockResource::mapMissionTokens([
            'Contractor' => [''],
        ]))->toBeNull();
    });
});
