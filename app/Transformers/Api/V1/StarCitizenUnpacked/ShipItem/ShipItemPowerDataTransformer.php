<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\ShipItemPowerData;
use League\Fractal\TransformerAbstract;

class ShipItemPowerDataTransformer extends TransformerAbstract
{

    public function transform(ShipItemPowerData $item): array
    {
        return array_filter([
            'power_base' => $item->power_base,
            'power_draw' => $item->power_draw,
            'throttleable' => $item->throttleable,
            'overclockable' => $item->overclockable,
            'overclock_threshold_min' => $item->overclock_threshold_min,
            'overclock_threshold_max' => $item->overclock_threshold_max,
            'overclock_performance' => $item->overclock_performance,
            'overpower_performance' => $item->overpower_performance,
            'power_to_em' => $item->power_to_em,
            'decay_rate_em' => $item->decay_rate_em,
        ]);
    }
}
