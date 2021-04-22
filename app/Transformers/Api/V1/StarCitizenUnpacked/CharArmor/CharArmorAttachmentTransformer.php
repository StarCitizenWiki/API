<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmorAttachment;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

class CharArmorAttachmentTransformer extends AbstractTranslationTransformer
{
    /**
     * @param CharArmorAttachment $mode
     *
     * @return array
     */
    public function transform(CharArmorAttachment $mode): array
    {
        return [
            'name' => $mode->name,
            'min_size' => $mode->min_size,
            'max_size' => $mode->max_size,
        ];
    }
}
