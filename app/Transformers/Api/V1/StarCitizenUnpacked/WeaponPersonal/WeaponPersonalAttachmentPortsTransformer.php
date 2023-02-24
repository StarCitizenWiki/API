<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachmentPort;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

class WeaponPersonalAttachmentPortsTransformer extends AbstractTranslationTransformer
{
    /**
     * @param WeaponPersonalAttachmentPort $port
     *
     * @return array
     */
    public function transform(WeaponPersonalAttachmentPort $port): array
    {
        return [
            'name' => $port->name,
            'position' => $port->position,
            'sizes' => [
                'min' => $port->min_size,
                'max' => $port->max_size,
            ],
        ];
    }
}
