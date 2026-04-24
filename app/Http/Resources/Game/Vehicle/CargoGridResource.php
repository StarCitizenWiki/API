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
        new OA\Property(property: 'uuid', description: 'Unique cargo grid identifier.', type: 'string', example: '102867b1-be6b-4a66-b228-4d2ab2f1c414', nullable: true),
        new OA\Property(property: 'class_name', description: 'SC class name of the cargo grid.', type: 'string', example: 'AEGS_Avenger_CargoGrid_Titan', nullable: true),
        new OA\Property(property: 'scu', description: 'Capacity in Standard Cargo Units.', type: 'number', example: 8),
        new OA\Property(property: 'capacity', description: 'Raw capacity value.', type: 'number', example: 8),
        new OA\Property(property: 'capacity_name', description: 'Unit label for capacity (e.g. SCU).', type: 'string', example: 'SCU', nullable: true),
        new OA\Property(property: 'is_open', description: 'Whether this is an open container grid.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'is_external', description: 'Whether this grid is externally accessible.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_closed', description: 'Whether this is a closed container grid.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'x', description: 'Grid dimension on the X axis.', type: 'number', example: 2.5),
        new OA\Property(property: 'y', description: 'Grid dimension on the Y axis.', type: 'number', example: 5),
        new OA\Property(property: 'z', description: 'Grid dimension on the Z axis.', type: 'number', example: 1.25),
        new OA\Property(property: 'min_size', description: 'Minimum cargo box dimensions.', properties: [
            new OA\Property(property: 'x', description: 'Minimum size on X axis.', type: 'number', example: 1.25),
            new OA\Property(property: 'y', description: 'Minimum size on Y axis.', type: 'number', example: 1.25),
            new OA\Property(property: 'z', description: 'Minimum size on Z axis.', type: 'number', example: 1.25),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'max_size', description: 'Maximum cargo box dimensions.', properties: [
            new OA\Property(property: 'x', description: 'Maximum size on X axis.', type: 'number', example: 2.5),
            new OA\Property(property: 'y', description: 'Maximum size on Y axis.', type: 'number', example: 2.5),
            new OA\Property(property: 'z', description: 'Maximum size on Z axis.', type: 'number', example: 1.25),
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
