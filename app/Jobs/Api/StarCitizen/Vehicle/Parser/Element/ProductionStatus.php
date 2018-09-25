<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 25.09.2018
 * Time: 12:51
 */

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus as ProductionStatusModel;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ProductionStatus
 */
class ProductionStatus extends BaseElement
{
    private const PRODUCTION_STATUS = 'production_status';

    private const PRODUCTION_STATUSES = [
        'Update Pass Scheduled',
        'Update pass scheduled',
        'Update pass scheduled.',
    ];

    private const PRODUCTION_STATUS_NORMALIZED = 'Update Pass Scheduled';

    /**
     * @return \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus
     */
    public function getProductionStatus(): ProductionStatusModel
    {
        app('Log')::debug('Getting Production Status');

        $status = $this->getNormalizedStatus();

        if (null === $status) {
            app('Log')::debug('Status not set in Matrix, returning default (undefined)');

            return ProductionStatusModel::find(1);
        }

        try {
            /** @var \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation $productionStatusTranslation */
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
     * @return \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus
     */
    private function createNewProductionStatus(): ProductionStatusModel
    {
        app('Log')::debug('Creating new Production Status');

        /** @var \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus $productionStatus */
        $productionStatus = ProductionStatusModel::create();

        $productionStatus->translations()->create(
            [
                'locale_code' => config('language.english'),
                'translation' => $this->getNormalizedStatus(),
            ]
        );

        app('Log')::debug('Production Status created');

        return $productionStatus;
    }

    /**
     * Returns the normalized Production Status
     *
     * @return string|null
     */
    private function getNormalizedStatus()
    {
        $status = $this->rawData->get(self::PRODUCTION_STATUS);

        if (null !== $status && is_string($status)) {
            $status = rtrim($status, '.');

            if (in_array($status, self::PRODUCTION_STATUSES)) {
                $status = self::PRODUCTION_STATUS_NORMALIZED;
            }
        }

        return $status;
    }
}
