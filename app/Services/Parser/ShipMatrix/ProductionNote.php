<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix;

use App\Models\StarCitizen\ShipMatrix\ProductionNote as ProductionNoteModel;
use App\Services\Parser\ShipMatrix\AbstractBaseElement as BaseElement;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ProductionNote
 */
class ProductionNote extends BaseElement
{
    protected const PRODUCTION_NOTE = 'production_note';

    private const PRODUCTION_STATUSES = [
        'Update Pass Scheduled',
        'Update pass scheduled',
        'Update pass scheduled.',
    ];

    private const PRODUCTION_STATUS_NORMALIZED = 'Update Pass Scheduled';

    /**
     * @throws ModelNotFoundException
     */
    public function getProductionNote(): ProductionNoteModel
    {
        $note = $this->getNormalizedStatus();
        if ($note === null) {
            app('Log')::debug('Production Note not set in Matrix, returning default (None)');

            return ProductionNoteModel::findOrFail(1);
        }

        try {
            return ProductionNoteModel::query()
                ->where('translation->'.config('language.english'), $note)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Production Note not found in DB');

            return $this->createNewProductionNote();
        }
    }

    /**
     * Returns the normalized Production Status
     */
    private function getNormalizedStatus(): ?string
    {
        $status = $this->rawData->get(self::PRODUCTION_NOTE);

        if ($status !== null && is_string($status)) {
            $status = rtrim($status, '.');

            if (in_array($status, self::PRODUCTION_STATUSES)) {
                $status = self::PRODUCTION_STATUS_NORMALIZED;
            }
        }

        return $status;
    }

    private function createNewProductionNote(): ProductionNoteModel
    {
        $translation = $this->getNormalizedStatus();
        $englishLocale = (string) config('language.english');

        if ($translation === null || $translation === '') {
            return ProductionNoteModel::findOrFail(1);
        }

        /** @var ProductionNoteModel|null $productionNote */
        $productionNote = ProductionNoteModel::query()
            ->where('translation->'.$englishLocale, $translation)
            ->first();

        if ($productionNote instanceof ProductionNoteModel) {
            return $productionNote;
        }

        /** @var ProductionNoteModel $productionNote */
        $productionNote = ProductionNoteModel::query()->create([
            'translation' => [
                $englishLocale => $translation,
            ],
        ]);

        app('Log')::debug('Production Note created', ['id' => $productionNote->id]);

        return $productionNote;
    }
}
