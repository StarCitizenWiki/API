<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\MiningLaser;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class MiningLaserTransformer extends AbstractCommodityTransformer
{

    public function transform($item): array
    {
        return [
            'hit_type' => $item->hit_type ?? '-',
            'energy_rate' => $item->energy_rate ?? '-',
            'full_damage_range' => $item->full_damage_range ?? '-',
            'zero_damage_range' => $item->zero_damage_range ?? '-',
            'heat_per_second' => $item->heat_per_second ?? '-',
            'damage' => $item->damage ?? '-',
            'modifier' => [
                'resistance' => $item->modifier_resistance ?? '-',
                'instability' => $item->modifier_instability ?? '-',
                'charge_window_size' => $item->modifier_charge_window_size ?? '-',
                'charge_window_rate' => $item->modifier_charge_window_rate ?? '-',
                'shatter_damage' => $item->modifier_shatter_damage ?? '-',
                'catastrophic_window_rate' => $item->modifier_catastrophic_window_rate ?? '-',
            ],
            'consumable_slots' => $item->consumable_slots ?? '-',
        ];
    }
}
