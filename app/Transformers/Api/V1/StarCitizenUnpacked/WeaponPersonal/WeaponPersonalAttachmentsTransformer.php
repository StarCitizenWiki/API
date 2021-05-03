<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAttachment;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

class WeaponPersonalAttachmentsTransformer extends AbstractTranslationTransformer
{
    /**
     * @param WeaponPersonalAttachment $port
     *
     * @return array
     */
    public function transform(WeaponPersonalAttachment $port): array
    {
        return [
            'name' => $port->name,
            'position' => $port->position,
            'size' => $port->size,
            'grade' => $port->grade,
        ];
    }
}
