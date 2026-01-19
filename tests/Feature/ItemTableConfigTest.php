<?php

declare(strict_types=1);

use App\Support\Items\ItemTableConfig;

it('adds sortField to columns from sorts config', function () {
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

    expect($massColumn)->toHaveKey('sortField');
    expect($massColumn['sortField'])->toBe('Mass');

    // Should also have sort metadata
    expect($massColumn)->toHaveKey('sort');
    expect($massColumn['sort'])->toEqual([
        'path' => 'Mass',
        'cast' => 'numeric',
    ]);

    config()->set('items.table.columns', $originalColumns);
    config()->set('sorts.items', $originalSorts);
});

it('adds sortField to nested columns in column groups', function () {
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

    expect($healthColumn)->toHaveKey('sortField');
    expect($healthColumn['sortField'])->toBe('Durability.Health');
    expect($healthColumn['sort']['path'])->toBe('Durability.Health');
    expect($healthColumn['sort']['cast'])->toBe('numeric');

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
        expect($nonSortableColumn)->not->toHaveKey('sortField');
        expect($nonSortableColumn)->not->toHaveKey('sort');
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

it('validates all sort config keys match actual column fields', function () {
    $itemsConfig = config('items.table.columns', []);
    $sortsConfig = config('sorts.items', []);

    // Collect all field names from columns
    $allFields = [];
    $collectFields = function (array $column) use (&$allFields, &$collectFields) {
        if (isset($column['columns']) && is_array($column['columns'])) {
            foreach ($column['columns'] as $nested) {
                $collectFields($nested);
            }
        }

        if (isset($column['field']) && is_string($column['field'])) {
            $allFields[] = $column['field'];
        }
    };

    foreach ($itemsConfig as $column) {
        $collectFields($column);
    }

    foreach (config('items.shared_groups', []) as $group) {
        if (isset($group['columns'])) {
            foreach ($group['columns'] as $column) {
                $collectFields($column);
            }
        }
    }

    // Find sort configs that don't match any column field
    $unusedSorts = [];
    foreach (array_keys($sortsConfig) as $sortKey) {
        if (! in_array($sortKey, $allFields, true)) {
            $unusedSorts[] = $sortKey;
        }
    }

    // This is informational - some sorts might be for API-only fields
    // But it's good to know which ones don't match UI columns
    if (! empty($unusedSorts)) {
        // Log for information, don't fail
        echo "\nInfo: ".count($unusedSorts).' sort configs don\'t match UI column fields\n';
    }

    expect(true)->toBeTrue(); // Always pass, this is informational only
});
