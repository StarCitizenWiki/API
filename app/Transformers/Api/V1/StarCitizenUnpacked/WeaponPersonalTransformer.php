<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\WeaponPersonal;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Collection;

class WeaponPersonalTransformer extends AbstractTranslationTransformer
{
    protected $availableIncludes = [
        'modes'
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
        return [
            'name' => $weapon->name,
            'description' => $this->getTranslation($weapon),
            'size' => $weapon->size,
            'manufacturer' => $weapon->manufacturer,
            'type' => $weapon->type,
            'class' => $weapon->class,
            'magazine_size' => $weapon->magazine_size ?? 0,
            'effective_range' => $weapon->effective_range ?? 0,
            'rof' => $weapon->rof ?? 0,
            'attachments' => [
                'optics' => $weapon->attachment_size_optics ?? 0,
                'barrel' => $weapon->attachment_size_barrel ?? 0,
                'underbarrel' => $weapon->attachment_size_underbarrel ?? 0,
            ],
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
