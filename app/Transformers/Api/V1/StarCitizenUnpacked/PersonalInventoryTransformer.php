<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\PersonalInventory;

class PersonalInventoryTransformer extends AbstractCommodityTransformer
{
    public function transform(PersonalInventory $item): array
    {
        return [
            'scu' => $item->scu,
        ];
    }
}
