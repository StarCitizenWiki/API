<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Game\ItemController;
use App\Models\Game\ItemData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ItemController implements FiltersJsonColumns trait', function () {
    $controller = new ItemController;

    expect(method_exists($controller, 'getJsonTableName'))->toBeTrue()
        ->and(method_exists($controller, 'getJsonColumnName'))->toBeTrue()
        ->and(method_exists($controller, 'jsonSort'))->toBeTrue()
        ->and(method_exists($controller, 'applyJsonFilter'))->toBeTrue()
        ->and(method_exists($controller, 'jsonExpression'))->toBeTrue();
});

test('getJsonTableName returns correct table name', function () {
    $controller = new ItemController;
    $reflection = new ReflectionMethod($controller, 'getJsonTableName');
    $reflection->setAccessible(true);

    $tableName = $reflection->invoke($controller);

    expect($tableName)->toBe('game_item_data');
});

test('getJsonColumnName returns correct column name', function () {
    $controller = new ItemController;
    $reflection = new ReflectionMethod($controller, 'getJsonColumnName');
    $reflection->setAccessible(true);

    $columnName = $reflection->invoke($controller);

    expect($columnName)->toBe('data');
});

test('jsonExpression builds correct expression', function () {
    $controller = new ItemController;
    $reflection = new ReflectionMethod($controller, 'jsonExpression');
    $reflection->setAccessible(true);

    $expression = $reflection->invoke($controller, 'stdItem.Mass', null);

    // Check for either PostgreSQL or SQLite syntax
    if (config('database.default') === 'pgsql') {
        expect($expression)->toBe("game_item_data.data #>> '{stdItem,Mass}'");
    } else {
        expect($expression)->toBe("json_extract(game_item_data.data, '$.stdItem.Mass')");
    }
});

test('jsonExpression builds correct expression with cast', function () {
    $controller = new ItemController;
    $reflection = new ReflectionMethod($controller, 'jsonExpression');
    $reflection->setAccessible(true);

    $expression = $reflection->invoke($controller, 'stdItem.Shield.MaxShieldHealth', 'numeric');

    // Check for either PostgreSQL or SQLite syntax
    if (config('database.default') === 'pgsql') {
        expect($expression)->toBe("(game_item_data.data #>> '{stdItem,Shield,MaxShieldHealth}')::numeric");
    } else {
        expect($expression)->toBe("CAST(json_extract(game_item_data.data, '$.stdItem.Shield.MaxShieldHealth') AS REAL)");
    }
});

test('laravelJsonColumn builds correct Laravel JSON path', function () {
    $controller = new ItemController;
    $reflection = new ReflectionMethod($controller, 'laravelJsonColumn');
    $reflection->setAccessible(true);

    $jsonColumn = $reflection->invoke($controller, 'game_item_data.data', 'stdItem.PowerConnection.PowerDraw');

    expect($jsonColumn)->toBe('game_item_data.data->stdItem->PowerConnection->PowerDraw');
});

test('applyJsonFilter filters items by JSON path with numeric cast', function () {
    // Create test items with different mass values
    ItemData::factory()->create([
        'name' => 'Light Item',
        'data' => ['stdItem' => ['Mass' => 50.0]],
    ]);

    ItemData::factory()->create([
        'name' => 'Heavy Item',
        'data' => ['stdItem' => ['Mass' => 150.0]],
    ]);

    $controller = new ItemController;
    $query = ItemData::query();

    // Use reflection to call protected method
    $reflection = new ReflectionMethod($controller, 'applyJsonFilter');
    $reflection->setAccessible(true);
    $reflection->invoke($controller, $query, 'stdItem.Mass', [50.0], 'numeric');

    $results = $query->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Light Item');
})->group('db-pgsql');

test('applyJsonFilter handles multiple filter values', function () {
    // Create test items
    ItemData::factory()->create([
        'name' => 'Item A',
        'data' => ['stdItem' => ['Mass' => 50.0]],
    ]);

    ItemData::factory()->create([
        'name' => 'Item B',
        'data' => ['stdItem' => ['Mass' => 100.0]],
    ]);

    ItemData::factory()->create([
        'name' => 'Item C',
        'data' => ['stdItem' => ['Mass' => 150.0]],
    ]);

    $controller = new ItemController;
    $query = ItemData::query();

    // Filter for multiple mass values
    $reflection = new ReflectionMethod($controller, 'applyJsonFilter');
    $reflection->setAccessible(true);
    $reflection->invoke($controller, $query, 'stdItem.Mass', [50.0, 100.0], 'numeric');

    $results = $query->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('name')->toArray())->toContain('Item A', 'Item B');
})->group('db-pgsql');

test('applyJsonFilter ignores null and empty values', function () {
    ItemData::factory()->create([
        'name' => 'Test Item',
        'data' => ['stdItem' => ['Mass' => 50.0]],
    ]);

    $controller = new ItemController;
    $query = ItemData::query();

    // Apply filter with null and empty values
    $reflection = new ReflectionMethod($controller, 'applyJsonFilter');
    $reflection->setAccessible(true);
    $reflection->invoke($controller, $query, 'stdItem.Mass', [null, '', 50.0], 'numeric');

    $results = $query->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Test Item');
})->group('db-pgsql');

test('applyColumnFilter filters regular columns', function () {
    ItemData::factory()->create(['type' => 'Weapon']);
    ItemData::factory()->create(['type' => 'Shield']);
    ItemData::factory()->create(['type' => 'Weapon']);

    $controller = new ItemController;
    $query = ItemData::query();

    // Filter by type column
    $reflection = new ReflectionMethod($controller, 'applyColumnFilter');
    $reflection->setAccessible(true);
    $reflection->invoke($controller, $query, 'type', 'Weapon');

    $results = $query->get();

    expect($results)->toHaveCount(2)
        ->and($results->every(fn ($item) => $item->type === 'Weapon'))->toBeTrue();
});
