<?php

declare(strict_types=1);

use App\Support\Items\ItemTableConfig;

it('adds sort metadata only for configured sortable fields', function (): void {
    $originalColumns = config('items.table.columns');
    $originalSorts = config('sorts.items');

    try {
        config()->set('items.table.columns', [
            ['title' => 'Mass', 'field' => 'mass'],
            [
                'title' => 'Durability',
                'columns' => [
                    ['title' => 'Health', 'field' => 'durability.health'],
                ],
            ],
            ['title' => 'Custom', 'field' => 'custom_value'],
        ]);
        config()->set('sorts.items', [
            'mass' => [
                'path' => 'Mass',
                'cast' => 'numeric',
            ],
            'durability.health' => [
                'path' => 'Durability.Health',
                'cast' => 'numeric',
            ],
        ]);

        $config = new ItemTableConfig;
        $result = $config->build(null);

        $columns = collect($result['columns'])
            ->flatMap(fn ($column) => $column['columns'] ?? [$column])
            ->values();

        $massColumn = $columns->first(fn ($column) => ($column['field'] ?? null) === 'mass');
        $healthColumn = $columns->first(fn ($column) => ($column['field'] ?? null) === 'durability.health');
        $customColumn = $columns->first(fn ($column) => ($column['field'] ?? null) === 'custom_value');

        expect($massColumn)->toMatchArray([
            'field' => 'mass',
            'sortField' => 'Mass',
            'sort' => [
                'path' => 'Mass',
                'cast' => 'numeric',
            ],
        ])
            ->and($healthColumn)->toMatchArray([
                'field' => 'durability.health',
                'sortField' => 'Durability.Health',
                'sort' => [
                    'path' => 'Durability.Health',
                    'cast' => 'numeric',
                ],
            ])
            ->and($result['title'])->toBe('Items')
            ->and($result['headerFilterOptionsMap'])->toMatchArray([
                'manufacturer.name' => 'manufacturer',
                'type' => 'type',
                'sub_type' => 'sub_type',
                'classification' => 'classification',
                'size' => 'size',
                'grade' => 'grade',
                'class' => 'class',
            ])
            ->and($customColumn)->not->toHaveKey('sortField')
            ->and($customColumn)->not->toHaveKey('sort');
    } finally {
        config()->set('items.table.columns', $originalColumns);
        config()->set('sorts.items', $originalSorts);
    }
});

it('inserts shared and additional columns before the api url column', function () {
    $originalColumns = config('items.table.columns');
    $originalSharedGroups = config('items.shared_groups');
    $originalTypeOverrides = config('items.type_overrides');
    $originalSorts = config('sorts.items');

    try {
        config()->set('items.table.columns', [
            ['title' => 'Grade', 'field' => 'grade'],
            ['title' => 'Class', 'field' => 'class'],
            ['title' => 'API Url', 'field' => 'uuid'],
        ]);
        config()->set('items.shared_groups', [
            'durability' => [
                'title' => 'Durability',
                'columns' => [
                    ['title' => 'Health', 'field' => 'durability.health'],
                ],
            ],
        ]);
        config()->set('items.type_overrides', [
            'TestType' => [
                'shared' => ['durability'],
                'shared_insert_at' => 2,
                'add_columns_insert_at' => 1,
                'add_columns' => [
                    ['title' => 'Signals', 'columns' => []],
                    ['title' => 'Damage', 'columns' => []],
                    ['title' => 'Penetration Resistance', 'columns' => []],
                ],
            ],
        ]);
        config()->set('sorts.items', []);

        $config = new ItemTableConfig;
        $result = $config->build('TestType');

        expect(collect($result['columns'])->pluck('title')->filter()->values()->all())->toBe([
            'Grade',
            'Class',
            'Signals',
            'Damage',
            'Penetration Resistance',
            'Durability',
            'API Url',
        ])->and($result['title'])->toBe('Test Type Items');
    } finally {
        config()->set('items.table.columns', $originalColumns);
        config()->set('items.shared_groups', $originalSharedGroups);
        config()->set('items.type_overrides', $originalTypeOverrides);
        config()->set('sorts.items', $originalSorts);
    }
});

it('keeps inserted columns ahead of the api url column for negative and oversized positions', function () {
    $originalColumns = config('items.table.columns');
    $originalTypeOverrides = config('items.type_overrides');
    $originalSorts = config('sorts.items');

    try {
        config()->set('items.table.columns', [
            ['title' => 'Name', 'field' => 'name'],
            ['title' => 'API Url', 'field' => 'uuid'],
        ]);
        config()->set('sorts.items', []);

        $config = new ItemTableConfig;

        config()->set('items.type_overrides', [
            'TestType' => [
                'add_columns' => [
                    ['title' => 'Custom', 'field' => 'custom.field'],
                ],
                'add_columns_insert_at' => -1,
            ],
        ]);

        $negativeInsertFields = collect($config->build('TestType')['columns'])->pluck('field')->values()->all();

        config()->set('items.type_overrides', [
            'TestType' => [
                'add_columns' => [
                    ['title' => 'Custom', 'field' => 'custom.field'],
                ],
                'add_columns_insert_at' => 999,
            ],
        ]);

        $oversizedInsertFields = collect($config->build('TestType')['columns'])->pluck('field')->values()->all();
        $finalConfig = $config->build('TestType');

        expect($negativeInsertFields)->toBe([
            'name',
            'custom.field',
            'uuid',
        ])->and($oversizedInsertFields)->toBe([
            'name',
            'custom.field',
            'uuid',
        ])->and($finalConfig['title'])->toBe('Test Type Items')
            ->and($finalConfig['columns'])->toHaveCount(3)
            ->and(collect($finalConfig['columns'])->pluck('field')->all())->toBe([
                'name',
                'custom.field',
                'uuid',
            ]);
    } finally {
        config()->set('items.table.columns', $originalColumns);
        config()->set('items.type_overrides', $originalTypeOverrides);
        config()->set('sorts.items', $originalSorts);
    }
});
