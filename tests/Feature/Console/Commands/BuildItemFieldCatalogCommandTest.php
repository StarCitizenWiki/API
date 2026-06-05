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
    ])
        ->and($byField['name'])
        ->type->toBe('string')
        ->array->toBeFalse()
        ->columnable->toBeTrue()
        ->schema->toBe('game_item')
        ->and($byField['size'])
        ->type->toBe('integer')
        ->nullable->toBeTrue()
        ->and($byField['ammunition.capacity'])
        ->type->toBe('integer')
        ->nullable->toBeTrue()
        ->array->toBeFalse()
        ->schema->toBe('ammunition')
        ->and($byField['vehicle_weapon.damage.alpha_total'])
        ->type->toBe('number')
        ->nullable->toBeTrue()
        ->schema->toBe('vehicle_weapon_damage')
        ->and($byField['tags'])
        ->type->toBe('string')
        ->array->toBeTrue()
        ->columnable->toBeTrue()
        ->and($byField['ports'])
        ->array->toBeTrue()
        ->columnable->toBeFalse()
        ->and($byField)->not->toHaveKey('ports.name');

    File::delete($outputPath);
});

it('marks only simple string and numeric arrays as columnable', function (): void {
    $inputPath = storage_path('framework/testing/item-field-catalog-simple-arrays.yaml');
    $outputPath = storage_path('framework/testing/item-field-catalog-simple-arrays.json');

    File::put($inputPath, <<<'YAML'
openapi: 3.0.0
components:
  schemas:
    game_item:
      properties:
        names:
          type: array
          items:
            type: string
        ratings:
          type: array
          items:
            type: number
        sizes:
          type: array
          items:
            type: integer
        mixed_numbers:
          type: array
          items:
            oneOf:
              - type: integer
              - type: number
        flags:
          type: array
          items:
            type: boolean
        ports:
          type: array
          items:
            type: object
            properties:
              name:
                type: string
YAML);
    File::delete($outputPath);

    $this->artisan('items:build-field-catalog', [
        '--input' => $inputPath,
        '--output' => $outputPath,
    ])
        ->assertExitCode(Command::SUCCESS);

    $fields = json_decode(File::get($outputPath), true, flags: JSON_THROW_ON_ERROR);
    $byField = collect($fields)->keyBy('field');

    expect($byField['names'])->columnable->toBeTrue()
        ->and($byField['ratings'])->columnable->toBeTrue()
        ->and($byField['sizes'])->columnable->toBeTrue()
        ->and($byField['mixed_numbers'])->columnable->toBeTrue()
        ->and($byField['flags'])->columnable->toBeFalse()
        ->and($byField['ports'])->columnable->toBeFalse();

    File::delete($inputPath);
    File::delete($outputPath);
});
