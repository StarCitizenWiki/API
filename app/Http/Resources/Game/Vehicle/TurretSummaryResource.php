<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_turret',
    title: 'Turret Summary',
    description: 'Manned or remote turret entry as provided by the ship data.',
    properties: [
        new OA\Property(property: 'size', type: 'integer', example: 5, nullable: true),
        new OA\Property(property: 'turret', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'gimballed', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'fixed', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'weapon_sizes', type: 'array', items: new OA\Items(type: 'integer', example: 5), nullable: true),
    ],
    type: 'object'
)]
class TurretSummaryResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        return array_filter([
            'size' => Arr::get($this->resource, 'Size'),
            'turret' => Arr::get($this->resource, 'Turret'),
            'gimballed' => Arr::get($this->resource, 'Gimballed'),
            'fixed' => Arr::get($this->resource, 'Fixed'),
            'weapon_sizes' => Arr::get($this->resource, 'WeaponSizes'),
        ], static fn ($value) => $value !== null && $value !== []);
    }
}
