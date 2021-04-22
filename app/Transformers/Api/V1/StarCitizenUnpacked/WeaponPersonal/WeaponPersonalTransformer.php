<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;
use League\Fractal\Resource\Collection;

class WeaponPersonalTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'modes',
        'shops'
    ];

    protected $defaultIncludes = [
        'modes'
    ];

    /**
     * @param WeaponPersonal $weapon
     *
     * @return array
     */
    public function transform(WeaponPersonal $weapon): array
    {
        $this->missingTranslations = [];

        return [
            'uuid' => $weapon->item->uuid,
            'name' => $weapon->item->name,
            'description' => $this->getTranslation($weapon),
            'size' => $weapon->item->size,
            'manufacturer' => $weapon->item->manufacturer,
            'type' => $weapon->item->type,
            'class' => $weapon->class,
            'magazine_size' => $weapon->magazine_size ?? 0,
            'effective_range' => $weapon->effective_range ?? 0,
            'rof' => $weapon->rof ?? 0,
            'attachments' => [
                'optics' => $weapon->attachment_size_optics ?? 0,
                'barrel' => $weapon->attachment_size_barrel ?? 0,
                'underbarrel' => $weapon->attachment_size_underbarrel ?? 0,
            ],
            'ammunition_speed' => $weapon->ammunition_speed ?? 0,
            'ammunition_range' => $weapon->ammunition_range ?? 0,
            'version' => config('api.sc_data_version'),
            'updated_at' => $weapon->updated_at,
            'missing_translations' => $this->missingTranslations,
        ];
    }

    /**
     * @param WeaponPersonal $weapon
     *
     * @return Collection
     */
    public function includeModes(WeaponPersonal $weapon): Collection
    {
        return $this->collection($weapon->modes, new WeaponPersonalModeTransformer());
    }
}
