<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_port_item',
    title: 'Equipped Port Item',
    description: 'Minimal item information sourced directly from the scunpacked ship data.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: 'e947b002-e65e-4637-90b7-c894925d6625'),
        new OA\Property(property: 'name', type: 'string', example: '<= PLACEHOLDER =>'),
        new OA\Property(property: 'class_name', type: 'string', example: 'LFSP_TYDT_S01_ComfortAir', nullable: true),
        new OA\Property(property: 'size', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'grade', type: 'integer', example: 3, nullable: true),
        new OA\Property(property: 'manufacturer', properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Tyler Design & Tech'),
            new OA\Property(property: 'code', type: 'string', example: 'TYDT'),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'type', type: 'string', example: 'LifeSupportGenerator', nullable: true),
        new OA\Property(property: 'sub_type', type: 'string', example: 'UNDEFINED', nullable: true),
        new OA\Property(property: 'link', type: 'string'),
    ],
    type: 'object'
)]
class PortItemResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        if ($this->resource === null || ! is_array($this->resource)) {
            return [];
        }

        $uuid = Arr::get($this->resource, 'UUID');

        return array_filter([
            'uuid' => $uuid,
            'name' => Arr::get($this->resource, 'Name'),
            'class_name' => Arr::get($this->resource, 'ClassName'),
            'size' => Arr::get($this->resource, 'Size'),
            'grade' => Arr::get($this->resource, 'Grade'),
            'manufacturer' => array_filter([
                'name' => Arr::get($this->resource, 'Manufacturer.Name'),
                'code' => Arr::get($this->resource, 'Manufacturer.Code'),
            ]) ?: null,
            'type' => Arr::get($this->resource, 'Type'),
            'sub_type' => Arr::get($this->resource, 'SubType'),
            $this->mergeWhen($uuid !== null, [
                'link' => $this->makeApiUrl(self::ITEMS_SHOW, $uuid),
            ]),
        ], static fn ($value) => $value !== null && $value !== []);
    }
}
