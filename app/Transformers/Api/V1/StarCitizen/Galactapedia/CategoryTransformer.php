<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer as TranslationTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

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
