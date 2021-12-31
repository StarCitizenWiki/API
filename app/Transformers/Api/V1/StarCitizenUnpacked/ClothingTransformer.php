<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\Clothing;

class ClothingTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'shops'
    ];

    /**
     * @param Clothing $clothing
     *
     * @return array
     */
    public function transform(Clothing $clothing): array
    {
        $this->missingTranslations = [];

        $data = [
            'uuid' => $clothing->item->uuid,
            'name' => $clothing->item->name,
            'description' => $this->getTranslation($clothing),
            'manufacturer' => $clothing->item->manufacturer,
            'type' => $clothing->item->type,
            'sub_type' => $clothing->item->sub_type,
            'clothing_type' => $clothing->type,
            'carrying_capacity' => $clothing->carrying_capacity,
            'volume' => [
                'width' => $clothing->item->volume->width,
                'height' => $clothing->item->volume->height,
                'length' => $clothing->item->volume->length,
                'volume' => $clothing->item->volume->volume,
            ],
        ];

        $baseModel = $clothing->baseModel;
        if ($baseModel !== null) {
            $data['base_model'] = (new ClothingLinkTransformer())->transform($baseModel);
        }

        $data += [
            'updated_at' => $clothing->updated_at,
            'missing_translations' => $this->missingTranslations,
            'resistances' => [
                'temperature' => [
                    'min' => $clothing->temp_resistance_min,
                    'max' => $clothing->temp_resistance_max,
                ],
            ],
            'version' => $clothing->version,
        ];

        return $data;
    }
}
