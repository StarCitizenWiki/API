<?php

declare(strict_types=1);

use App\Support\Items\ItemTableConfig;

it('adds sortfield to columns from sorts config', function () {
    $originalColumns = config('items.table.columns');
    $originalSorts = config('sorts.items');

    config()->set('items.table.columns', [
        ['title' => 'Mass', 'field' => 'mass'],
    ]);
    config()->set('sorts.items', $originalSorts);

    $config = new ItemTableConfig;
    $result = $config->build(null);

    // Find mass column
    $massColumn = collect($result['columns'])
        ->flatMap(fn ($col) => $col['columns'] ?? [$col])
        ->first(fn ($col) => ($col['field'] ?? null) === 'mass');

    expect($massColumn)->toHaveKey('sortField')
        ->and($massColumn['sortField'])->toBe('Mass')
        ->and($massColumn)->toHaveKey('sort')
        ->and($massColumn['sort'])->toEqual([
            'path' => 'Mass',
            'cast' => 'numeric',
        ]);

    // Should also have sort metadata

    config()->set('items.table.columns', $originalColumns);
    config()->set('sorts.items', $originalSorts);
});

it('adds sortfield to nested columns in column groups', function () {
    $originalColumns = config('items.table.columns');
    $originalSorts = config('sorts.items');

    config()->set('items.table.columns', [
        [
            'title' => 'Durability',
            'columns' => [
                ['title' => 'Health', 'field' => 'durability.health'],
            ],
        ],
    ]);
    config()->set('sorts.items', $originalSorts);

    $config = new ItemTableConfig;
    $result = $config->build(null);

    $healthColumn = collect($result['columns'])
        ->flatMap(fn ($col) => $col['columns'] ?? [$col])
        ->first(fn ($col) => ($col['field'] ?? null) === 'durability.health');

    expect($healthColumn)->toHaveKey('sortField')
        ->and($healthColumn['sortField'])->toBe('Durability.Health')
        ->and($healthColumn['sort']['path'])->toBe('Durability.Health')
        ->and($healthColumn['sort']['cast'])->toBe('numeric');

    config()->set('items.table.columns', $originalColumns);
    config()->set('sorts.items', $originalSorts);
});

it('does not modify columns without matching sort config', function () {
    $config = new ItemTableConfig;
    $result = $config->build(null);

    $sortsConfig = config('sorts.items');

    // Find a column that doesn't have a sort config (if any exist)
    $nonSortableColumn = collect($result['columns'])
        ->flatMap(fn ($col) => $col['columns'] ?? [$col])
        ->first(function ($col) use ($sortsConfig) {
            $field = $col['field'] ?? null;

            return $field && ! isset($sortsConfig[$field]);
        });

    if ($nonSortableColumn !== null) {
        expect($nonSortableColumn)->not->toHaveKey('sortField')
            ->and($nonSortableColumn)->not->toHaveKey('sort');
    }
});

it('resolves type overrides by matches aliases', function () {
    $originalColumns = config('items.table.columns');
    $originalHeaderFilterMap = config('items.table.header_filter_options_map');
    $originalTypeOverrides = config('items.type_overrides');
    $originalSorts = config('sorts.items');

    config()->set('items.table.columns', [
        ['title' => 'Name', 'field' => 'name'],
    ]);
    config()->set('items.table.header_filter_options_map', []);
    config()->set('items.type_overrides', [
        'fps-armor' => [
            'title' => 'FPS Armor',
            'matches' => [
                'Char_Armor_Helmet',
                'Char_Armor_Torso',
            ],
        ],
    ]);
    config()->set('sorts.items', []);

    $config = new ItemTableConfig;
    $result = $config->build('CHAR_ARMOR_HELMET');

    expect($result['title'])->toBe('FPS Armor');

    config()->set('items.table.columns', $originalColumns);
    config()->set('items.table.header_filter_options_map', $originalHeaderFilterMap);
    config()->set('items.type_overrides', $originalTypeOverrides);
    config()->set('sorts.items', $originalSorts);
});

it('validates sortable item columns map to complete sort metadata', function () {
    $itemsConfig = config('items.table.columns', []);
    $sortsConfig = config('sorts.items', []);

    $allFields = [];
    $collectFields = function (array $column) use (&$allFields, &$collectFields): void {
        if (isset($column['columns']) && is_array($column['columns'])) {
            foreach ($column['columns'] as $nestedColumn) {
                if (is_array($nestedColumn)) {
                    $collectFields($nestedColumn);
                }
            }
        }

        $field = $column['field'] ?? null;
        if (is_string($field) && $field !== '') {
            $allFields[] = $field;
        }
    };

    foreach ($itemsConfig as $column) {
        if (is_array($column)) {
            $collectFields($column);
        }
    }

    foreach (config('items.shared_groups', []) as $group) {
        if (! is_array($group) || ! isset($group['columns']) || ! is_array($group['columns'])) {
            continue;
        }

        foreach ($group['columns'] as $column) {
            if (is_array($column)) {
                $collectFields($column);
            }
        }
    }

    $sortableFields = array_values(array_filter(
        array_unique($allFields),
        static fn (string $field): bool => isset($sortsConfig[$field])
    ));

    expect($sortableFields)->not->toBeEmpty();

    foreach ($sortableFields as $field) {
        $sortConfig = $sortsConfig[$field];

        expect($sortConfig)->toHaveKeys(['path', 'cast'])
            ->and($sortConfig['path'])->toBeString()->not->toBe('')
            ->and($sortConfig['cast'])->toBeString()->not->toBe('');
    }
});
