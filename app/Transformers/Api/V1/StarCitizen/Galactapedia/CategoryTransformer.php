<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Category;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;

/**
 * Manufacturer Transformer
 */
class CategoryTransformer extends V1Transformer
{
    /**
     * @param Category $category
     *
     * @return array
     */
    public function transform(Category $category): array
    {
        return [
            'id' => $category->cig_id,
            'name' => $category->name,
        ];
    }
}
