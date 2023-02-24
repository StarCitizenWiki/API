<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Shield;

use App\Models\StarCitizenUnpacked\ShipItem\Shield\Shield;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;
use League\Fractal\Resource\Collection;

class ShieldTransformer extends AbstractCommodityTransformer
{
    protected array $defaultIncludes = [
        'absorption',
    ];

    public function transform(Shield $item): array
    {
        return [
            'max_shield_health' => $item->max_shield_health,
            'max_shield_regen' => $item->max_shield_regen,
            'decay_ratio' => $item->decay_ratio,
            'regen_delay' => [
                'downed' => $item->downed_regen_delay,
                'damage' => $item->damage_regen_delay,
            ],
            'max_reallocation' => $item->max_reallocation,
            'reallocation_rate' => $item->reallocation_rate,
            'hardening' => [
                'factor' => $item->shield_hardening_factor,
                'duration' => $item->shield_hardening_duration,
                'cooldown' => $item->shield_hardening_cooldown,
            ],
        ];
    }

    public function includeAbsorption(Shield $item): Collection
    {
        return $this->collection($item->absorptions, new ShieldAbsorptionTransformer());
    }
}
