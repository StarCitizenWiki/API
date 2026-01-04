<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix;

use App\Models\StarCitizen\ShipMatrix\ProductionStatus as ProductionStatusModel;
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
        $status = $this->rawData->get(self::PRODUCTION_STATUS);

        if ($status === null) {
            app('Log')::debug('Status not set in Matrix, returning default (undefined)');

            return ProductionStatusModel::findOrFail(1);
        }

        try {
            return ProductionStatusModel::query()
                ->where('translation->'.config('language.english'), $status)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Production Status not found in DB');

            return $this->createNewProductionStatus();
        }
    }

    private function createNewProductionStatus(): ProductionStatusModel
    {
        $slug = Str::slug($this->rawData->get(self::PRODUCTION_STATUS));
        $translation = $this->rawData->get(self::PRODUCTION_STATUS);

        /** @var ProductionStatusModel $productionStatus */
        $productionStatus = ProductionStatusModel::query()->firstOrCreate(
            ['slug' => $slug],
            ['slug' => $slug]
        );

        if ($translation !== null && $translation !== '') {
            $productionStatus->setTranslation('translation', config('language.english'), $translation);
            $productionStatus->save();
        }

        app('Log')::debug('Production Status created', ['id' => $productionStatus->id]);

        return $productionStatus;
    }
}
