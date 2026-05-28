<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_description_data',
    title: 'Item Description Data',
    description: 'Data found in the description of an item, e.g. "Carrying Capacity: 2kµSCU".',
    properties: [
        new OA\Property(property: 'name', description: 'The label of the description entry, e.g. "Carrying Capacity".', type: 'string'),
        new OA\Property(property: 'value', description: 'The value of the description entry, e.g. "2kµSCU".', type: 'string'),
        new OA\Property(property: 'type', description: 'Deprecated: Use "value" key.', type: 'string', deprecated: true),
    ],
    type: 'object'
)]
/** @param object $resource Description data entry with ->name and ->value properties */
class ItemDescriptionDataResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'type' => $this->value,
        ];
    }
}
