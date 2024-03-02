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
        new OA\Property(
            property: 'armor_type',
            description: 'Property is set if resource is of type "armor".',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'clothing_type',
            description: 'Property is set if resource is of type "clothing".',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_reduction',
            description: 'Damage reduction in % this resource provides',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'temp_resistance_min',
            description: 'The minimum temperature this resource protects against',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'temp_resistance_max',
            description: 'The maximum temperature this resource protects against',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'resistances',
            description: 'List of resistances this resource has',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/clothing_resistance_v2'),
            nullable: true,
        ),
        new OA\Property(property: 'base_variant', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class ClothingResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return parent::validIncludes() + [
            'shops',
            'shops.items',
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        $typeKey = 'armor_type';
        if (str_contains($this->type, 'Char_Clothing')) {
            $typeKey = 'clothing_type';
        }

        return [
            $typeKey => $this->clothing_type,
            'damage_reduction' => $this->damage_reduction,
            'temp_resistance_min' => $this->temp_resistance_min,
            'temp_resistance_max' => $this->temp_resistance_max,
            'resistances' => ClothingResistanceResource::collection($this->damageResistances),
        ];
    }
}
