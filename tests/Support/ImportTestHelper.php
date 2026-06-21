<?php

declare(strict_types=1);

use App\Models\Game\Manufacturer;
use Illuminate\Console\Command;

if (! function_exists('createManufacturer')) {
    /**
     * Create a game Manufacturer with explicit name/code. Shared across the
     * Import* tests where each test otherwise hand-rolls the factory call.
     */
    function createManufacturer(string $name, string $code, ?string $uuid = null): Manufacturer
    {
        return Manufacturer::factory()->create([
            'uuid' => $uuid ?? fake()->uuid(),
            'name' => $name,
            'code' => $code,
        ]);
    }
}

if (! function_exists('assertFailsOnMissingVersion')) {
    /**
     * Assert that a `game:import-*` command refuses to run for an unknown
     * version code. Centralized because the assertion text appears verbatim
     * in six import test files; a wording change would otherwise break all of
     * them at once.
     */
    function assertFailsOnMissingVersion(string $command): void
    {
        test()->artisan($command, ['version' => 'missing'])
            ->assertExitCode(Command::FAILURE)
            ->expectsOutput('Game version "missing" does not exist. Please create it first.');
    }
}

if (! function_exists('baseVehiclePayload')) {
    /**
     * Minimal vehicle payload accepted by ImportVehicleData, with optional
     * extra keys merged in. Hoisted here so ImportVehiclesTest and
     * ImportVehicleDataMaxMedicalTierTest share the same skeleton.
     */
    function baseVehiclePayload(string $uuid, string $manufacturerUuid, array $extra = []): array
    {
        return array_merge([
            'UUID' => $uuid,
            'ClassName' => 'TEST_SHIP',
            'Name' => 'Test Ship',
            'Career' => 'Combat',
            'Role' => 'Fighter',
            'IsVehicle' => false,
            'IsGravlev' => false,
            'IsSpaceship' => true,
            'Size' => 2,
            'Manufacturer' => [
                'UUID' => $manufacturerUuid,
                'Name' => 'Test Manufacturer',
            ],
        ], $extra);
    }
}
