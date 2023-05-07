<?php

declare(strict_types=1);

namespace App\Http\Resources\SC;

use App\Http\Resources\AbstractTranslationResource;
use App\Http\Resources\SC\Item\ItemResource;
use Illuminate\Http\Request;

class FoodResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [
            'effects',
            'item.shops',
            'item.shops.items',
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return (new ItemResource($this->item))->toArray($request) + [
                'nutritional_density_rating' => $this->nutritional_density_rating,
                'hydration_efficacy_index' => $this->hydration_efficacy_index,
                'container_type' => $this->container_type,
                'one_shot_consume' => $this->one_shot_consume,
                'can_be_reclosed' => $this->can_be_reclosed,
                'discard_when_consumed' => $this->discard_when_consumed,
                'effects' => $this->effects->map(function($effect) {
                    return $effect->name;
                }),
                'updated_at' => $this->updated_at,
                'version' => $this->version,
            ];
    }
}
