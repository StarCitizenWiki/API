<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\ShipItemDurabilityData;
use League\Fractal\TransformerAbstract;

class ShipItemDurabilityDataTransformer extends TransformerAbstract
{
    public function transform(ShipItemDurabilityData $item): array
    {
        return array_filter([
            'health' => $item->health,
            'max_lifetime' => $item->max_lifetime,
        ]);
    }
}
