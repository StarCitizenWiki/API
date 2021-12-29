<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;
use League\Fractal\Resource\Collection;

class WeaponPersonalTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'shops',
        'modes',
        'damages',
        'attachments',
        'attachmentPorts',
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
            'type' => $weapon->weapon_type,
            'sub_type' => $weapon->item->sub_type,
            'class' => $weapon->weapon_class,
            'magazine_type' => $weapon->magazineType,
            'magazine_size' => $weapon->magazine->max_ammo_count ?? 0,
            'effective_range' => $weapon->effective_range ?? 0,
            'damage_per_shot' => $weapon->ammunition->damage ?? 0,
            'rof' => $weapon->rof ?? 0,
            'updated_at' => $weapon->updated_at,
            'missing_translations' => $this->missingTranslations,
            'ammunition' => [
                'size' => $weapon->ammunition->size ?? 0,
                'lifetime' => $weapon->ammunition->lifetime ?? 0,
                'speed' => $weapon->ammunition->speed ?? 0,
                'range' => $weapon->ammunition->range ?? 0,
            ],
            'volume' => [
                'width' => $weapon->item->volume->width,
                'height' => $weapon->item->volume->height,
                'length' => $weapon->item->volume->length,
                'volume' => $weapon->item->volume->volume,
            ],
            'version' => $weapon->version,
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

    public function includeDamages(WeaponPersonal $weapon): Collection
    {
        return $this->collection($weapon->ammunition->damages, new WeaponPersonalAmmunitionDamageTransformer());
    }

    public function includeAttachments(WeaponPersonal $weapon): Collection
    {
        return $this->collection($weapon->attachments, new WeaponPersonalAttachmentsTransformer());
    }

    public function includeAttachmentPorts(WeaponPersonal $weapon): Collection
    {
        return $this->collection($weapon->attachmentPorts, new WeaponPersonalAttachmentPortsTransformer());
    }
}
