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

it('emits a synthetic locale-suffixed sibling for translation-object array fields', function (): void {
    $inputPath = storage_path('framework/testing/item-field-catalog-translation-array.yaml');
    $outputPath = storage_path('framework/testing/item-field-catalog-translation-array.json');

    File::put($inputPath, <<<'YAML'
openapi: 3.0.0
components:
  schemas:
    game_item:
      type: object
      properties:
        description:
          oneOf:
            - type: string
            - type: array
              items:
                $ref: '#/components/schemas/translation'
    translation:
      title: Grouped Translations
      description: Translations of an entity
      type: object
      properties:
        en_EN:
          type: string
        de_DE:
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

    // The synthetic `description.en_EN` sibling is columnable so the column
    // builder can offer the localized text as a selectable column.
    expect($byField)->toHaveKey('description.en_EN')
        ->and($byField['description.en_EN']['columnable'])->toBeTrue()
        ->and($byField['description.en_EN']['array'])->toBeFalse()
        ->and($byField['description.en_EN']['title'])->toBe('Description (en_EN)')
        // The original `description` field is preserved (still non-columnable,
        // matching the underlying oneOf shape).
        ->and($byField)->toHaveKey('description')
        ->and($byField['description']['columnable'])->toBeFalse();

    File::delete($inputPath);
    File::delete($outputPath);
});

it('skips fields collected through a deprecated parent schema', function (): void {
    $inputPath = storage_path('framework/testing/item-field-catalog-deprecated-parent.yaml');
    $outputPath = storage_path('framework/testing/item-field-catalog-deprecated-parent.json');

    File::put($inputPath, <<<'YAML'
openapi: 3.0.0
components:
  schemas:
    game_item:
      type: object
      properties:
        name:
          type: string
        clothing:
          oneOf:
            -
              $ref: '#/components/schemas/clothing'
            -
              $ref: '#/components/schemas/suit_armor'
        suit_armor:
          $ref: '#/components/schemas/suit_armor'
    clothing:
      title: Clothing
      description: 'DEPRECATED: Use suit_armor instead.'
      deprecated: true
      type: object
      properties:
        slot:
          type: string
    suit_armor:
      title: 'Suit Armor'
      type: object
      properties:
        slot:
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

    // Fields reached through the deprecated `clothing` ref are skipped entirely
    // (no path pollution / no rename). The path `suit_armor.slot` is reached
    // directly through the live `suit_armor` property on game_item.
    expect($byField)->not->toHaveKey('clothing.slot')
        ->and($byField)->toHaveKey('suit_armor.slot')
        ->and($byField['suit_armor.slot']['schemas'])->toContain('suit_armor')
        ->and($byField['suit_armor.slot']['schemas'])->not->toContain('clothing');

    File::delete($inputPath);
    File::delete($outputPath);
});
