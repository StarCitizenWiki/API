<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\ArticleProperty;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;

/**
 * Manufacturer Transformer
 */
class PropertyTransformer extends V1Transformer
{
    /**
     * @param ArticleProperty $property
     *
     * @return array
     */
    public function transform(ArticleProperty $property): array
    {
        return [
            'name' => $property->name,
            'value' => $property->content,
        ];
    }
}
