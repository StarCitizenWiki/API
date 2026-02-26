<?php

declare(strict_types=1);

it('defines weight column as a single volume field with unit formatter', function (): void {
    $columns = collect(config('items.shared_groups.occupancy.columns', []));
    $weightColumns = $columns
        ->where('title', 'Weight')
        ->values();
    $weightColumn = $weightColumns->first();

    expect($weightColumns)->toHaveCount(1)
        ->and($weightColumn)->toBe([
            'title' => 'Weight',
            'field' => 'dimension.volume_converted',
            'formatter' => 'volumeWithUnit',
            'formatterParams' => [
                'unitField' => 'dimension.volume_converted_unit',
            ],
        ]);
});
