<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_cargo_grid',
    title: 'Vehicle Cargo Grid',
    description: 'Cargo grid slots from ship data, presented as-is.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '102867b1-be6b-4a66-b228-4d2ab2f1c414', nullable: true),
        new OA\Property(property: 'class_name', type: 'string', example: 'AEGS_Avenger_CargoGrid_Titan', nullable: true),
        new OA\Property(property: 'scu', type: 'number', example: 8),
        new OA\Property(property: 'capacity', type: 'number', example: 8),
        new OA\Property(property: 'capacity_name', type: 'string', example: 'SCU', nullable: true),
        new OA\Property(property: 'is_open', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'is_external', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_closed', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'x', type: 'number', example: 2.5),
        new OA\Property(property: 'y', type: 'number', example: 5),
        new OA\Property(property: 'z', type: 'number', example: 1.25),
        new OA\Property(property: 'min_size', properties: [
            new OA\Property(property: 'x', type: 'number', example: 1.25),
            new OA\Property(property: 'y', type: 'number', example: 1.25),
            new OA\Property(property: 'z', type: 'number', example: 1.25),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'max_size', properties: [
            new OA\Property(property: 'x', type: 'number', example: 2.5),
            new OA\Property(property: 'y', type: 'number', example: 2.5),
            new OA\Property(property: 'z', type: 'number', example: 1.25),
        ], type: 'object', nullable: true),
    ],
    type: 'object'
)]
class CargoGridResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        return array_filter([
            'uuid' => Arr::get($this->resource, 'UUID'),
            'class_name' => Arr::get($this->resource, 'Class'),
            'scu' => Arr::get($this->resource, 'SCU'),
            'capacity' => Arr::get($this->resource, 'Capacity'),
            'capacity_name' => Arr::get($this->resource, 'CapacityName'),
            'is_open' => Arr::get($this->resource, 'IsOpenContainer'),
            'is_external' => Arr::get($this->resource, 'IsExternalContainer'),
            'is_closed' => Arr::get($this->resource, 'IsClosedContainer'),
            'x' => Arr::get($this->resource, 'X'),
            'y' => Arr::get($this->resource, 'Y'),
            'z' => Arr::get($this->resource, 'Z'),
            'min_size' => $this->sizeBlock('MinSize'),
            'max_size' => $this->sizeBlock('MaxSize'),
        ], static fn ($value) => $value !== null && $value !== []);
    }

    private function sizeBlock(string $key): ?array
    {
        $data = Arr::get($this->resource, $key);

        if (! is_array($data)) {
            return null;
        }

        $block = array_filter([
            'x' => Arr::get($data, 'X'),
            'y' => Arr::get($data, 'Y'),
            'z' => Arr::get($data, 'Z'),
        ], static fn ($value) => $value !== null);

        return $block === [] ? null : $block;
    }
}
