<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;

it('preserves lookup configuration', function (string $class, string $table, bool $timestamps, string $routeKey, array $fillable): void {
    $model = new $class;

    expect($model->getTable())->toBe($table)
        ->and($model->usesTimestamps())->toBe($timestamps)
        ->and($model->getRouteKeyName())->toBe($routeKey)
        ->and($model->getFillable())->toBe($fillable)
        ->and($model->translatable)->toBe(['translation']);
})->with([
    [Focus::class, 'shipmatrix_vehicle_foci', false, 'slug', ['slug', 'translation']],
    [Size::class, 'shipmatrix_vehicle_sizes', false, 'slug', ['slug', 'translation']],
    [Type::class, 'shipmatrix_vehicle_types', true, 'slug', ['slug', 'translation']],
    [ProductionStatus::class, 'shipmatrix_production_statuses', true, 'slug', ['slug', 'translation']],
    [ProductionNote::class, 'shipmatrix_production_notes', true, 'id', ['translation']],
]);
