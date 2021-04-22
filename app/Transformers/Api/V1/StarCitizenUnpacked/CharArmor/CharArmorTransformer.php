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

        return [
            'uuid' => $armor->item->uuid,
            'name' => $armor->item->name,
            'description' => $this->getTranslation($armor),
            'size' => $armor->item->size,
            'manufacturer' => $armor->item->manufacturer,
            'type' => $armor->item->type,
            'sub_type' => $armor->item->sub_type,
            'armor_type' => $armor->armor_type,
            'version' => config('api.sc_data_version'),
            'updated_at' => $armor->updated_at,
            'missing_translations' => $this->missingTranslations,
            'resistances' => [
                'temperature' => [
                    'min' => $armor->temp_resistance_min,
                    'max' => $armor->temp_resistance_max,
                ],
                'physical' => [
                    'multiplier' => $armor->resistance_physical_multiplier,
                    'threshold' => $armor->resistance_physical_threshold,
                ],
                'energy' => [
                    'multiplier' => $armor->resistance_energy_multiplier,
                    'threshold' => $armor->resistance_energy_threshold,
                ],
                'distortion' => [
                    'multiplier' => $armor->resistance_distortion_multiplier,
                    'threshold' => $armor->resistance_distortion_threshold,
                ],
                'thermal' => [
                    'multiplier' => $armor->resistance_thermal_multiplier,
                    'threshold' => $armor->resistance_thermal_threshold,
                ],
                'biochemical' => [
                    'multiplier' => $armor->resistance_biochemical_multiplier,
                    'threshold' => $armor->resistance_biochemical_threshold,
                ],
                'stun' => [
                    'multiplier' => $armor->resistance_stun_multiplier,
                    'threshold' => $armor->resistance_stun_threshold,
                ]
            ]
        ];
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
}
