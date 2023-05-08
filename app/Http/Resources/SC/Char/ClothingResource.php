<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ClothingResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'resistances',
            'ports',
            'shops',
            'shops.items',
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $typeKey = 'armor_type';
        $route = self::UNPACKED_ARMOR_SHOW;
        if (str_contains($this->item->type, 'Char_Clothing')) {
            $route = self::UNPACKED_CLOTHES_SHOW;
            $typeKey = 'clothing_type';
        }

        $data = [
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

        return $data;
    }
}
