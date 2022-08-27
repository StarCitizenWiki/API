<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\Food;

use App\Models\StarCitizenUnpacked\Food\Food;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class FoodTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'shops'
    ];

    /**
     * @param Food $food
     *
     * @return array
     */
    public function transform(Food $food): array
    {
        $this->missingTranslations = [];

        return [
            'uuid' => $food->item->uuid,
            'name' => $food->item->name,
            'description' => $this->getTranslation($food),
            'manufacturer' => $food->item->manufacturer,
            'type' => $food->item->type,
            'sub_type' => $food->item->sub_type,
            'volume' => [
                'width' => $food->item->volume->width,
                'height' => $food->item->volume->height,
                'length' => $food->item->volume->length,
                'volume' => $food->item->volume->volume,
            ],
            'nutritional_density_rating' => $food->nutritional_density_rating,
            'hydration_efficacy_index' => $food->hydration_efficacy_index,
            'effects' => $food->effects->pluck('name'),
            'updated_at' => $food->updated_at,
            'version' => $food->version,
        ];
    }
}
