<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_part',
    title: 'Vehicle Structure Part',
    description: 'Structural hierarchy for the vehicle with damage caps.',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Nose'),
        new OA\Property(property: 'display_name', type: 'string', example: 'Nose'),
        new OA\Property(property: 'damage_max', type: 'number', example: 2500, nullable: true),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_part'),
            nullable: true
        ),
    ],
    type: 'object'
)]
class PartResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        return array_filter([
            'name' => Arr::get($this->resource, 'Name'),
            'display_name' => Arr::get($this->resource, 'DisplayName'),
            'damage_max' => Arr::get($this->resource, 'DamageMax'),
            $this->mergeWhen(Arr::has($this->resource, 'Children'), [
                'children' => self::collection(Arr::get($this->resource, 'Children', [])),
            ]),
        ], static fn ($value) => $value !== null && $value !== []);
    }
}
