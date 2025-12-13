<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix;

use App\Models\StarCitizen\ProductionStatus\ProductionStatus as ProductionStatusModel;
use App\Models\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use App\Services\Parser\ShipMatrix\AbstractBaseElement as BaseElement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Class ProductionStatus
 */
class ProductionStatus extends BaseElement
{
    private const PRODUCTION_STATUS = 'production_status';

    /**
     * @throws ModelNotFoundException
     */
    public function getProductionStatus(): ProductionStatusModel
    {
        app('Log')::debug('Getting Production Status');

        $status = $this->rawData->get(self::PRODUCTION_STATUS);

        if ($status === null) {
            app('Log')::debug('Status not set in Matrix, returning default (undefined)');

            return ProductionStatusModel::findOrFail(1);
        }

        try {
            /** @var ProductionStatusTranslation $productionStatusTranslation */
            $productionStatusTranslation = ProductionStatusTranslation::query()->where(
                'translation',
                $status
            )->where(
                'locale_code',
                config('language.english')
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Production Status not found in DB');

            return $this->createNewProductionStatus();
        }

        return $productionStatusTranslation->productionStatus;
    }

    private function createNewProductionStatus(): ProductionStatusModel
    {
        app('Log')::debug('Creating new Production Status');

        $slug = Str::slug($this->rawData->get(self::PRODUCTION_STATUS));
        $translation = $this->rawData->get(self::PRODUCTION_STATUS);

        // Race-safe: slug has unique constraint
        /** @var ProductionStatusModel $productionStatus */
        $productionStatus = ProductionStatusModel::query()->firstOrCreate(
            ['slug' => $slug],
            ['slug' => $slug]
        );

        // Race-safe translation update
        $productionStatus->translations()->updateOrCreate(
            ['locale_code' => config('language.english')],
            ['translation' => $translation]
        );

        app('Log')::debug('Production Status created', ['id' => $productionStatus->id]);

        return $productionStatus;
    }
}
