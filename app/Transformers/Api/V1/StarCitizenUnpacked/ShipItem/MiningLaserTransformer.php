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
            'energy_rate' => $item->energy_rate ?? 0,
            'full_damage_range' => $item->full_damage_range ?? 0,
            'zero_damage_range' => $item->zero_damage_range ?? 0,
            'heat_per_second' => $item->heat_per_second ?? 0,
            'damage' => $item->damage ?? 0,
            'modifier' => [
                'resistance' => $item->modifier_resistance ?? 0,
                'instability' => $item->modifier_instability ?? 0,
                'charge_window_size' => $item->modifier_charge_window_size ?? 0,
                'charge_window_rate' => $item->modifier_charge_window_rate ?? 0,
                'shatter_damage' => $item->modifier_shatter_damage ?? 0,
                'catastrophic_window_rate' => $item->modifier_catastrophic_window_rate ?? 0,
            ],
            'consumable_slots' => $item->consumable_slots ?? 0,
        ];
    }
}
