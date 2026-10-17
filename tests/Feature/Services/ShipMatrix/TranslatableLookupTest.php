<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\ProductionStatus as ProductionStatusModel;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size as VehicleSize;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type as VehicleType;
use App\Services\Parser\ShipMatrix\TranslatableLookup;
use Illuminate\Support\Collection;

dataset('shipMatrixLookups', [
    'vehicle size' => [VehicleSize::class, 'size'],
    'vehicle type' => [VehicleType::class, 'type'],
    'production status' => [ProductionStatusModel::class, 'production_status'],
]);

describe('translatable lookups', function (): void {
    it('finds the model by its english translation', function (string $modelClass, string $arrayKey): void {
        $existing = $modelClass::query()->create([
            'slug' => 'known-value',
            'translation' => ['en' => 'Known Value'],
        ]);

        $resolved = (new TranslatableLookup(new Collection([$arrayKey => 'Known Value']), $modelClass, $arrayKey))->get();

        expect($resolved->id)->toBe($existing->id)
            ->and($modelClass::query()->count())->toBe(1);
    })->with('shipMatrixLookups');

    it('falls back to the seeded default row when the matrix key is not set', function (string $modelClass, string $arrayKey): void {
        $default = $modelClass::query()->forceCreate([
            'id' => 1,
            'slug' => 'undefined',
        ]);

        $resolved = (new TranslatableLookup(new Collection([]), $modelClass, $arrayKey))->get();

        expect($resolved->id)->toBe($default->id);
    })->with('shipMatrixLookups');

    it('creates a slug and translation for an unknown value and reuses it afterwards', function (string $modelClass, string $arrayKey): void {
        $created = (new TranslatableLookup(new Collection([$arrayKey => 'Capital Ship']), $modelClass, $arrayKey))->get();
        $resolvedAgain = (new TranslatableLookup(new Collection([$arrayKey => 'Capital Ship']), $modelClass, $arrayKey))->get();

        expect($resolvedAgain->id)->toBe($created->id)
            ->and($created->getTranslation('translation', 'en', false))->toBe('Capital Ship');

        $this->assertDatabaseHas((new $modelClass)->getTable(), ['slug' => 'capital-ship']);
    })->with('shipMatrixLookups');

    it('creates a slug-only model for a blank value', function (string $modelClass, string $arrayKey): void {
        $created = (new TranslatableLookup(new Collection([$arrayKey => '']), $modelClass, $arrayKey))->get();

        expect($created->slug)->toBe('')
            ->and($created->getTranslations('translation'))->toBe([]);
    })->with('shipMatrixLookups');
});
