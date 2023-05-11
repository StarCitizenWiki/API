<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char\PersonalWeapon;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'iron_sight_v2',
    title: 'Iron Sight',
    description: 'FPS Iron Sight',
    properties: [
        new OA\Property(property: 'magnification', type: 'string', nullable: true),
        new OA\Property(property: 'optic_type', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class IronSightResource extends AbstractBaseResource
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
            'magnification' => $this->magnification ?? null,
            'optic_type' => $this->type ?? null,
        ];
    }
}
