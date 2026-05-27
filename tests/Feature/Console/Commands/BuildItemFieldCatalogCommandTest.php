<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

it('builds an item field catalog from the OpenAPI game item schema', function (): void {
    $outputPath = storage_path('framework/testing/item-fields.json');

    File::delete($outputPath);

    $this->artisan('items:build-field-catalog', [
        '--output' => $outputPath,
    ])
        ->assertExitCode(Command::SUCCESS);

    expect(File::exists($outputPath))->toBeTrue();

    $fields = json_decode(File::get($outputPath), true, flags: JSON_THROW_ON_ERROR);
    $byField = collect($fields)->keyBy('field');

    expect($byField)->toHaveKeys([
        'name',
        'size',
        'ammunition.capacity',
        'vehicle_weapon.damage.alpha_total',
    ]);

    expect($byField['name'])
        ->type->toBe('string')
        ->array->toBeFalse()
        ->columnable->toBeTrue()
        ->schema->toBe('game_item');

    expect($byField['size'])
        ->type->toBe('integer')
        ->nullable->toBeTrue();

    expect($byField['ammunition.capacity'])
        ->type->toBe('integer')
        ->nullable->toBeTrue()
        ->array->toBeFalse()
        ->schema->toBe('ammunition');

    expect($byField['vehicle_weapon.damage.alpha_total'])
        ->type->toBe('number')
        ->nullable->toBeTrue()
        ->schema->toBe('vehicle_weapon_damage');

    expect($byField['tags'])
        ->type->toBe('string')
        ->array->toBeTrue()
        ->columnable->toBeFalse();

    expect($byField)->not->toHaveKey('ports.name');

    File::delete($outputPath);
});
