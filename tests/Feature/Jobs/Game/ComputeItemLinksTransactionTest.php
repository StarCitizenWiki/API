<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeItemSetItems;
use App\Jobs\Game\ComputeItemVariantGradeLinks;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

it('restores previous links after an insert fails and preserves version isolation', function (string $job, string $table, string $peerColumn, bool $set): void {
    $version = GameVersion::factory()->create();
    $otherVersion = GameVersion::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $common = [
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'is_player_relevant' => true,
        'size' => 1,
    ];
    $first = ItemData::factory()->create($common + [
        'name' => $set ? 'Test Helmet' : 'First cooler',
        'class_name' => $set ? 'test_helmet_01' : 'cooler_first',
        'classification' => $set ? 'FPS.Armor.Helmet' : 'Ship.Cooler',
        'grade' => 1,
    ]);
    $second = ItemData::factory()->create($common + [
        'name' => $set ? 'Test Core' : 'Second cooler',
        'class_name' => $set ? 'test_core_01' : 'cooler_second',
        'classification' => $set ? 'FPS.Armor.Torso' : 'Ship.Cooler',
        'grade' => 2,
    ]);
    $other = ItemData::factory()->create(['game_version_id' => $otherVersion->id]);
    DB::table($table)->insert([
        ['item_data_id' => $first->id, $peerColumn => $first->id],
        ['item_data_id' => $other->id, $peerColumn => $other->id],
    ]);

    $fail = true;
    $insertObserved = false;
    DB::listen(function (QueryExecuted $query) use ($table, &$fail, &$insertObserved): void {
        if ($fail && str_starts_with(strtolower($query->sql), 'insert into "'.$table.'"')) {
            $insertObserved = true;
            $fail = false;
            throw new RuntimeException('Injected failure after insert');
        }
    });

    try {
        (new $job($version->id))->handle();
        $this->fail('The injected failure did not occur.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Injected failure after insert');
    }

    expect($insertObserved)->toBeTrue();
    $rows = fn () => DB::table($table)->orderBy('item_data_id')->orderBy($peerColumn)
        ->get(['item_data_id', $peerColumn])->map(fn ($row) => (array) $row)->all();
    expect($rows())->toBe([
        ['item_data_id' => $first->id, $peerColumn => $first->id],
        ['item_data_id' => $other->id, $peerColumn => $other->id],
    ]);

    (new $job($version->id))->handle();

    expect($rows())->toBe([
        ['item_data_id' => $first->id, $peerColumn => $second->id],
        ['item_data_id' => $second->id, $peerColumn => $first->id],
        ['item_data_id' => $other->id, $peerColumn => $other->id],
    ]);
})->with([
    'set items' => [ComputeItemSetItems::class, 'game_item_set_items', 'set_item_data_id', true],
    'grade links' => [ComputeItemVariantGradeLinks::class, 'game_item_variant_grade_items', 'grade_item_data_id', false],
]);
