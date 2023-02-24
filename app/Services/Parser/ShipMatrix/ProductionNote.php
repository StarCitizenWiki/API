<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix;

use App\Models\StarCitizen\ProductionNote\ProductionNote as ProductionNoteModel;
use App\Models\StarCitizen\ProductionNote\ProductionNoteTranslation;
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
     * @return ProductionNoteModel
     *
     * @throws ModelNotFoundException
     */
    public function getProductionNote(): ProductionNoteModel
    {
        app('Log')::debug('Getting Production Note');

        $note = $this->getNormalizedStatus();
        if (null === $note) {
            app('Log')::debug('Production Note not set in Matrix, returning default (None)');

            return ProductionNoteModel::findOrFail(1);
        }

        try {
            /** @var ProductionNoteTranslation $productionNoteTranslation */
            $productionNoteTranslation = ProductionNoteTranslation::query()->where(
                'translation',
                $note
            )->where(
                'locale_code',
                config('language.english')
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Production Note not found in DB');

            return $this->createNewProductionNote();
        }

        return $productionNoteTranslation->productionNote;
    }

    /**
     * Returns the normalized Production Status
     *
     * @return string|null
     */
    private function getNormalizedStatus(): ?string
    {
        $status = $this->rawData->get(self::PRODUCTION_NOTE);

        if (null !== $status && is_string($status)) {
            $status = rtrim($status, '.');

            if (in_array($status, self::PRODUCTION_STATUSES)) {
                $status = self::PRODUCTION_STATUS_NORMALIZED;
            }
        }

        return $status;
    }

    /**
     * @return ProductionNoteModel
     */
    private function createNewProductionNote(): ProductionNoteModel
    {
        app('Log')::debug('Creating new Production Note');

        /** @var ProductionNoteModel $productionNote */
        $productionNote = ProductionNoteModel::create();

        $productionNote->translations()->create(
            [
                'locale_code' => config('language.english'),
                'translation' => $this->getNormalizedStatus(),
            ]
        );

        app('Log')::debug('Production Note created');

        return $productionNote;
    }
}
