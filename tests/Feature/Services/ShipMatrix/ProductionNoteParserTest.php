<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\ProductionNote as ProductionNoteModel;
use App\Services\Parser\ShipMatrix\ProductionNote as ProductionNoteParser;
use Illuminate\Support\Collection;

it('creates and reuses a production note by english translation', function (): void {
    $lowercaseRawData = new Collection([
        'production_note' => 'Update pass scheduled.',
    ]);
    $titleCaseRawData = new Collection([
        'production_note' => 'Update Pass Scheduled',
    ]);

    $firstNote = (new ProductionNoteParser($lowercaseRawData))->getProductionNote();
    $secondNote = (new ProductionNoteParser($titleCaseRawData))->getProductionNote();

    expect($firstNote->id)->toBe($secondNote->id)
        ->and($firstNote->getTranslation('translation', 'en', false))->toBe('Update Pass Scheduled');

    $this->assertDatabaseCount('shipmatrix_production_notes', 1);
});

it('falls back to the default production note when the status is missing or blank', function (): void {
    $defaultNote = ProductionNoteModel::query()->create([
        'translation' => [
            'en' => 'None',
        ],
    ]);

    $missingStatusNote = (new ProductionNoteParser(new Collection([])))->getProductionNote();
    $blankStatusNote = (new ProductionNoteParser(new Collection([
        'production_note' => '',
    ])))->getProductionNote();

    expect($missingStatusNote->id)->toBe($defaultNote->id)
        ->and($missingStatusNote->getTranslation('translation', 'en', false))->toBe('None')
        ->and($blankStatusNote->id)->toBe($defaultNote->id);
});
