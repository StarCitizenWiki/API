<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'clothing_v2',
    title: 'Clothing',
    description: 'Clothes or Armors',
    properties: [
        new OA\Property(property: 'armor_type', type: 'string', nullable: true),
        new OA\Property(property: 'clothing_type', type: 'string', nullable: true),
        new OA\Property(property: 'damage_reduction', type: 'double', nullable: true),
        new OA\Property(property: 'temp_resistance_min', type: 'double', nullable: true),
        new OA\Property(property: 'temp_resistance_max', type: 'double', nullable: true),
        new OA\Property(
            property: 'resistances',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/clothing_resistance_v2'),
            nullable: true,
        ),
        new OA\Property(property: 'base_model', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class ClothingResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'resistances',
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
        $route = self::ARMOR_SHOW;
        if (str_contains($this->item->type, 'Char_Clothing')) {
            $route = self::CLOTHES_SHOW;
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
