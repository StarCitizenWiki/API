<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\Affiliation;
use League\Fractal\TransformerAbstract;

class AffiliationTransformer extends TransformerAbstract
{
    public function transform(Affiliation $affiliation): array
    {
        return [
            'id' => $affiliation->cig_id,
            'name' => $affiliation->name,
            'code' => $affiliation->code,
            'color' => $affiliation->color,
        ];
    }
}
