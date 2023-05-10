<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_durability_data_v2',
    title: 'Item Durability Data',
    properties: [
        new OA\Property(property: 'health', type: 'double', nullable: true),
        new OA\Property(property: 'max_lifetime', type: 'double', nullable: true),
        new OA\Property(property: 'repairable', type: 'boolean', nullable: true),
        new OA\Property(property: 'salvageable', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
class ItemDurabilityDataResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'health' => $this->health,
            'max_lifetime' => $this->max_lifetime,
            'repairable' => $this->repairable,
            'salvageable' => $this->salvageable,
        ];
    }
}
