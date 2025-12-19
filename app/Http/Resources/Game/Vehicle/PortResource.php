<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_port',
    title: 'Vehicle Port / Hardpoint',
    description: 'Ports (hardpoints) as defined in scunpacked ship files. Nested ports are supported.',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'hardpoint_weapon_class2_nose'),
        new OA\Property(property: 'position', type: 'string', example: 'left', nullable: true),
        new OA\Property(property: 'sizes', properties: [
            new OA\Property(property: 'min', type: 'integer', example: 1, nullable: true),
            new OA\Property(property: 'max', type: 'integer', example: 1, nullable: true),
        ], type: 'object'),
        new OA\Property(property: 'class_name', type: 'string', example: 'LFSP_TYDT_S01_ComfortAir', nullable: true),
        new OA\Property(property: 'health', type: 'number', example: 300, nullable: true),
        new OA\Property(
            property: 'compatible_types',
            description: 'Port compatibility straight from the ship data.',
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'type', type: 'string', example: 'LifeSupportGenerator', nullable: true),
                new OA\Property(property: 'sub_type', type: 'string', example: 'UNDEFINED', nullable: true),
            ], type: 'object'),
            nullable: true
        ),
        new OA\Property(property: 'equipped_item', ref: '#/components/schemas/game_vehicle_port_item', nullable: true),
        new OA\Property(
            property: 'ports',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_port'),
            nullable: true
        ),
    ],
    type: 'object'
)]
class PortResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        return array_filter([
            'name' => Arr::get($this->resource, 'Name'),
            'position' => Arr::get($this->resource, 'Position'),
            'sizes' => [
                'min' => Arr::get($this->resource, 'Sizes.Min'),
                'max' => Arr::get($this->resource, 'Sizes.Max'),
            ],
            'class_name' => Arr::get($this->resource, 'ClassName'),
            'health' => Arr::get($this->resource, 'Health'),
            'compatible_types' => $this->buildCompatibleTypes(),
            $this->mergeWhen(Arr::has($this->resource, 'EquippedItem'), [
                'equipped_item' => new PortItemResource(Arr::get($this->resource, 'EquippedItem')),
            ]),
            $this->mergeWhen(Arr::has($this->resource, 'Ports'), [
                'ports' => self::collection(Arr::get($this->resource, 'Ports', [])),
            ]),
        ], static fn ($value) => $value !== null && $value !== []);
    }

    private function buildCompatibleTypes(): ?array
    {
        $types = collect(Arr::get($this->resource, 'CompatibleTypes', []))
            ->map(fn (array $type): array => array_filter([
                'type' => Arr::get($type, 'Type'),
                'sub_type' => Arr::get($type, 'SubType'),
            ], static fn ($value) => $value !== null && $value !== ''));

        return $types->isEmpty() ? null : $types->values()->all();
    }
}
