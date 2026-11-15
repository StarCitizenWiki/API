<?php

declare(strict_types=1);

use App\Support\Blueprints\BlueprintTableConfig;
use App\Support\Missions\MissionTableConfig;
use App\Support\Resources\CommodityTableConfig;
use App\Support\Starmap\StarmapLocationTableConfig;
use App\Support\TableColumns;

it('preserves API link widths in each table', function (): void {
    foreach ([new BlueprintTableConfig, new MissionTableConfig, new CommodityTableConfig, new StarmapLocationTableConfig] as $config) {
        $columns = $config->build()['columns'];
        $column = collect($columns)->firstWhere('title', 'API Url');
        $expected = TableColumns::apiUrlColumn($config instanceof MissionTableConfig ? null : 100);
        expect($column)->toBe($expected);
    }
});

it('preserves boolean labels and values', function (): void {
    expect(TableColumns::booleanOptions())->toBe([
        ['value' => '', 'label' => 'All'],
        ['value' => 'true', 'label' => 'Yes'],
        ['value' => 'false', 'label' => 'No'],
    ])->and(TableColumns::booleanOptions('Default')[0]['label'])->toBe('Default');
});
