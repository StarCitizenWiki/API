<?php

declare(strict_types=1);

uses(Tests\TestCase::class);

it('defines weight column as a single volume field with unit formatter', function (): void {
    $columns = config('items.shared_groups.occupancy.columns', []);
    $weightColumn = collect($columns)->firstWhere('title', 'Weight');

    expect($weightColumn)->not->toBeNull()
        ->and($weightColumn['field'] ?? null)->toBe('dimension.volume_converted')
        ->and($weightColumn['formatter'] ?? null)->toBe('volumeWithUnit')
        ->and(data_get($weightColumn, 'formatterParams.unitField'))->toBe('dimension.volume_converted_unit')
        ->and($weightColumn)->not->toHaveKey('columns');
});
