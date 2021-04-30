<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\ShipItemDistortionData;
use League\Fractal\TransformerAbstract;

class ShipItemDistortionDataTransformer extends TransformerAbstract
{
    public function transform(ShipItemDistortionData $item): array
    {
        return array_filter([
            'decay_rate' => $item->decay_rate,
            'maximum' => $item->maximum,
            'overload_ratio' => $item->overload_ratio,
            'recovery_ratio' => $item->recovery_ratio,
            'recovery_time' => $item->recovery_time,
        ]);
    }
}
