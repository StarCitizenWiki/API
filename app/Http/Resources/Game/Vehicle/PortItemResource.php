<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_port_item',
    title: 'Equipped Port Item',
    type: 'object',

)]
class PortItemResource extends AbstractBaseResource
{
    use ExtractsJsonData;

    public function toArray(Request $request): array
    {
        $itemData = $this->data?->first();

        if ($itemData === null) {
            return [];
        }

        return [
            'uuid' => $this->uuid,
            'name' => $itemData->name,
            'class_name' => $itemData->class_name,
            'type' => $itemData->type,
            'sub_type' => $itemData->sub_type,
            'link' => $this->makeApiUrl(self::ITEMS_SHOW, $this->uuid),
            'size' => $itemData->size,
            'mass' => $this->extractFromStdItem($itemData, 'Mass'),
            'grade' => match ($itemData->grade) {
                1 => 'A',
                2 => 'B',
                3 => 'C',
                4 => 'D',
                default => $this->extractFromStdItem($itemData, 'DescriptionData.Grade') ?? $itemData->grade,
            },
            'class' => $itemData->class ?? $this->extractFromStdItem($itemData, 'DescriptionData.Class'),

            $this->mergeWhen($itemData->manufacturer !== null, [
                'manufacturer' => new ManufacturerLinkResource($itemData->manufacturer),
            ]),
        ];
    }
}
