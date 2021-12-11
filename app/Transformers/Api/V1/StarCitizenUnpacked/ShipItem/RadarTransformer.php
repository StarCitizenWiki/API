<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\Radar;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class RadarTransformer extends AbstractCommodityTransformer
{

    public function transform(Radar $item): array
    {
        return [
            'detection_lifetime' => $item->detection_lifetime,
            'altitude_ceiling' => $item->altitude_ceiling,
            'enable_cross_section_occlusion' => $item->enable_cross_section_occlusion,
        ];
    }
}
