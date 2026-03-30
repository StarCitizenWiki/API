<?php

declare(strict_types=1);

use App\Services\Parser\ShipMatrix\ProductionNote as ProductionNoteParser;
use Illuminate\Support\Collection;

it('creates and reuses a production note by english translation', function (): void {
    $rawData = new Collection([
        'production_note' => 'Update pass scheduled.',
    ]);

    $firstNote = (new ProductionNoteParser($rawData))->getProductionNote();
    $secondNote = (new ProductionNoteParser($rawData))->getProductionNote();

    expect($firstNote->id)->toBe($secondNote->id)
        ->and($firstNote->getTranslation('translation', 'en', false))->toBe('Update Pass Scheduled');

    $this->assertDatabaseCount('shipmatrix_production_notes', 1);
});
