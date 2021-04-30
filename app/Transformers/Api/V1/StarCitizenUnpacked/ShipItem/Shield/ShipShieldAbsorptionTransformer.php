<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Shield;

use App\Models\StarCitizenUnpacked\ShipItem\Shield\ShieldAbsorption;
use League\Fractal\TransformerAbstract;

class ShipShieldAbsorptionTransformer extends TransformerAbstract
{
    public function transform(ShieldAbsorption $item): array
    {
        return [
            $item->type => [
                'min' => $item->min,
                'max' => $item->max,
            ],
        ];
    }
}
