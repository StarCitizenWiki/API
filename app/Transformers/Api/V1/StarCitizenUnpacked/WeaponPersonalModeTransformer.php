<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\WeaponPersonalMode;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

class WeaponPersonalModeTransformer extends AbstractTranslationTransformer
{
    /**
     * @param WeaponPersonalMode $mode
     *
     * @return array
     */
    public function transform(WeaponPersonalMode $mode): array
    {
        return [
            'mode' => $mode->mode,
            'rpm' => $mode->rpm,
            'dps' => $mode->dps,
        ];
    }
}
