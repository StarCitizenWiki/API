<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmorResistance;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

class CharArmorResistanceTransformer extends AbstractTranslationTransformer
{
    /**
     * @param CharArmorResistance $resistance
     *
     * @return array
     */
    public function transform(CharArmorResistance $resistance): array
    {
        return [
            'type' => $resistance->type,
            'multiplier' => $resistance->multiplier,
            'threshold' => $resistance->threshold,
        ];
    }
}
