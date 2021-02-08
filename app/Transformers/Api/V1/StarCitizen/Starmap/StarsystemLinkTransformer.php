<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

/**
 * Class Starsystem Link Transformer
 */
class StarsystemLinkTransformer extends V1Transformer
{
    /**
     * @param Starsystem $starsystem
     *
     * @return array
     */
    public function transform(Starsystem $starsystem): array
    {
        return [
            'name' => $starsystem->name,
            'code' => $starsystem->code,
            'api_url' => $this->makeApiUrl(self::STARMAP_STARSYSTEM_SHOW, $starsystem->getRouteKey()),
        ];
    }
}
