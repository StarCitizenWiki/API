<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus as ProductionStatusModel;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Class ProductionStatus
 */
class ProductionStatus extends BaseElement
{
    private const PRODUCTION_STATUS = 'production_status';

    /**
     * @return ProductionStatusModel
     *
     * @throws ModelNotFoundException
     */
    public function getProductionStatus(): ProductionStatusModel
    {
        app('Log')::debug('Getting Production Status');

        $status = $this->rawData->get(self::PRODUCTION_STATUS);

        if (null === $status) {
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

    /**
     * @return ProductionStatusModel
     */
    private function createNewProductionStatus(): ProductionStatusModel
    {
        app('Log')::debug('Creating new Production Status');

        /** @var ProductionStatusModel $productionStatus */
        $productionStatus = ProductionStatusModel::create(
            [
                'slug' => Str::slug($this->rawData->get(self::PRODUCTION_STATUS)),
            ]
        );

        $productionStatus->translations()->create(
            [
                'locale_code' => config('language.english'),
                'translation' => $this->rawData->get(self::PRODUCTION_STATUS),
            ]
        );

        app('Log')::debug('Production Status created');

        return $productionStatus;
    }
}
