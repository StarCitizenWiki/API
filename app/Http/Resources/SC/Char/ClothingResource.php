<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractTranslationResource;
use App\Http\Resources\SC\Item\ItemResource;
use Illuminate\Http\Request;

class ClothingResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [
            'item.ports',
            'resistances',
            'item.ports',
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
        $typeKey = 'armor_type';
        $route = self::UNPACKED_ARMOR_SHOW;
        if (str_contains($this->item->type, 'Char_Clothing')) {
            $route = self::UNPACKED_CLOTHES_SHOW;
            $typeKey = 'clothing_type';
        }

        $data = (new ItemResource($this->item))->toArray($request) + [
                $typeKey => $this->type,
                'damage_reduction' => $this->damage_reduction,
                'temp_resistance_min' => $this->temp_resistance_min,
                'temp_resistance_max' => $this->temp_resistance_max,
                'resistances' => ClothingResistanceResource::collection($this->whenLoaded('resistances')),
            ];

        $baseModel = $this->baseModel;
        if ($baseModel !== null && $baseModel->item->name !== $this->item->name) {
            $data['base_model'] = $this->makeApiUrl($route, $baseModel->item_uuid);
        }

        $data += [
            'updated_at' => $this->updated_at,
            'version' => $this->version,
        ];

        return $data;
    }
}
