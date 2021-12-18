<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;
use League\Fractal\Resource\Collection;

class CharArmorTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'attachments',
        'shops'
    ];

    /**
     * @param CharArmor $armor
     *
     * @return array
     */
    public function transform(CharArmor $armor): array
    {
        $this->missingTranslations = [];

        $data = [
            'uuid' => $armor->item->uuid,
            'name' => $armor->item->name,
            'description' => $this->getTranslation($armor),
            'size' => $armor->item->size,
            'manufacturer' => $armor->item->manufacturer,
            'type' => $armor->item->type,
            'sub_type' => $armor->item->sub_type,
            'armor_type' => $armor->armor_type,
            'carrying_capacity' => $armor->carrying_capacity,
            'damage_reduction' => $armor->damage_reduction,
            'volume' => [
                'width' => $armor->item->volume->width,
                'height' => $armor->item->volume->height,
                'length' => $armor->item->volume->length,
                'volume' => $armor->item->volume->volume,
            ],
        ];

        $baseModel = $armor->baseModel;
        if ($baseModel !== null) {
            $data['base_model'] = (new CharArmorLinkTransformer())->transform($baseModel);
        }

        $data += [
            'updated_at' => $armor->updated_at,
            'missing_translations' => $this->missingTranslations,
            'resistances' => $this->mapResistances($armor),
            'version' => $armor->version,
        ];

        return $data;
    }

    /**
     * @param CharArmor $armor
     *
     * @return Collection
     */
    public function includeAttachments(CharArmor $armor): Collection
    {
        return $this->collection($armor->attachments, new CharArmorAttachmentTransformer());
    }

    /**
     * Re-formats the resistances into multiple arrays
     *
     * @param CharArmor $armor
     * @return array[]
     */
    private function mapResistances(CharArmor $armor): array
    {
        $mapped = $armor->resistances->keyBy('type')->map(function ($resistance) {
            return [
                'multiplier' => $resistance['multiplier'],
                'threshold' => $resistance['threshold'],
            ];
        });

        return [
                'temperature' => [
                    'min' => $armor->temp_resistance_min,
                    'max' => $armor->temp_resistance_max,
                ],
            ] + $mapped->toArray();
    }
}
