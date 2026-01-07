<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters items by type on the web route', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $widget = Item::factory()->create();
    ItemData::factory()
        ->for($widget)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Widget One',
            'type' => 'Widget',
            'class_name' => 'widget_one',
            'classification' => 'Test',
            'data' => [],
        ]);

    $gadget = Item::factory()->create();
    ItemData::factory()
        ->for($gadget)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Gadget One',
            'type' => 'Gadget',
            'class_name' => 'gadget_one',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->get(route('web.items.type', ['type' => 'Widget']));

    $response->assertOk()
        ->assertViewIs('items.index')
        ->assertViewHas('initialTableData', function (array $payload) use ($widget): bool {
            return ($payload['data'][0]['uuid'] ?? null) === $widget->uuid
                && count($payload['data']) === 1;
        })
        ->assertViewHas('pageTitle', 'Widget Items')
        ->assertViewHas('tableColumns', function (array $columns): bool {
            return collect($columns)->pluck('field')->doesntContain('type');
        })
        ->assertViewHas('headerFilterOptionsMap', function (array $map): bool {
            return ! array_key_exists('type', $map);
        });
});

it('includes shared group columns for types with shared configuration', function () {
    config(['items.shared_groups' => [
        'testGroup' => [
            'title' => 'Test Group',
            'columns' => [
                ['title' => 'Shared Field', 'field' => 'shared.field'],
            ],
        ],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
    ]]);

    $response = $this->get(route('web.items.type', ['type' => 'TestType']));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        foreach ($columns as $column) {
            if (isset($column['columns'])) {
                foreach ($column['columns'] as $nestedColumn) {
                    if (($nestedColumn['field'] ?? null) === 'shared.field') {
                        return true;
                    }
                }
            }
        }

        return false;
    });
});

it('applies shared group overrides correctly', function () {
    config(['items.shared_groups' => [
        'testGroup' => [
            'title' => 'Original Title',
            'columns' => [
                ['title' => 'Field', 'field' => 'test.field'],
            ],
        ],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
        'shared_overrides' => [
            'testGroup' => [
                'title' => 'Overridden Title',
            ],
        ],
    ]]);

    $response = $this->get(route('web.items.type', ['type' => 'TestType']));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        foreach ($columns as $column) {
            if (isset($column['title']) && $column['title'] === 'Overridden Title') {
                return true;
            }
        }

        return false;
    });
});

it('inserts shared groups at positive index', function () {
    config(['items.shared_groups.testGroup' => [
        'title' => 'Test Group',
        'columns' => [['title' => 'Shared', 'field' => 'shared.field']],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
        'shared_insert_at' => 2,
    ]]);

    $response = $this->get(route('web.items.type', ['type' => 'TestType']));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        return ($columns[2]['title'] ?? null) === 'Test Group';
    });
});

it('inserts add_columns at negative index', function () {
    config(['items.type_overrides.TestType' => [
        'add_columns' => [
            ['title' => 'Custom', 'field' => 'custom.field'],
        ],
        'add_columns_insert_at' => -1,
    ]]);

    $response = $this->get(route('web.items.type', ['type' => 'TestType']));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        $customIndex = null;
        $viewButtonIndex = null;

        foreach ($columns as $index => $column) {
            if (($column['field'] ?? null) === 'custom.field') {
                $customIndex = $index;
            }
            if (($column['formatter'] ?? null) === 'viewButton') {
                $viewButtonIndex = $index;
            }
        }

        return $customIndex !== null
            && $viewButtonIndex !== null
            && $customIndex < $viewButtonIndex;
    });
});

it('keeps view button at end when inserting columns', function () {
    config(['items.type_overrides.TestType' => [
        'add_columns' => [
            ['title' => 'Custom', 'field' => 'custom.field'],
        ],
        'add_columns_insert_at' => 999,
    ]]);

    $response = $this->get(route('web.items.type', ['type' => 'TestType']));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        $lastColumn = end($columns);

        return ($lastColumn['formatter'] ?? null) === 'viewButton';
    });
});

it('maintains backwards compatibility when no insert_at specified', function () {
    config(['items.shared_groups.testGroup' => [
        'title' => 'Test Group',
        'columns' => [['title' => 'Shared', 'field' => 'shared.field']],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
    ]]);

    $response = $this->get(route('web.items.type', ['type' => 'TestType']));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        foreach ($columns as $index => $column) {
            if (($column['title'] ?? null) === 'Test Group') {
                return $index >= 9;
            }
        }

        return false;
    });
});

it('handles both shared and add_columns with different positions', function () {
    config(['items.shared_groups.testGroup' => [
        'title' => 'Shared Group',
        'columns' => [['title' => 'Shared', 'field' => 'shared.field']],
    ]]);

    config(['items.type_overrides.TestType' => [
        'shared' => ['testGroup'],
        'shared_insert_at' => 3,
        'add_columns' => [
            ['title' => 'Custom', 'field' => 'custom.field'],
        ],
        'add_columns_insert_at' => 7,
    ]]);

    $response = $this->get(route('web.items.type', ['type' => 'TestType']));

    $response->assertSuccessful();
    $response->assertViewHas('tableColumns', function ($columns) {
        $sharedIndex = null;
        $customIndex = null;

        foreach ($columns as $index => $column) {
            if (($column['title'] ?? null) === 'Shared Group') {
                $sharedIndex = $index;
            }
            if (($column['field'] ?? null) === 'custom.field') {
                $customIndex = $index;
            }
        }

        return $sharedIndex === 3 && $customIndex > $sharedIndex;
    });
});
