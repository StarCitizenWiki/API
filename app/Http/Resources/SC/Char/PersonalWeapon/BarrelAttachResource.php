<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char\PersonalWeapon;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'barrel_attach_v2',
    title: 'Barrel Attach',
    description: 'Suppressors',
    properties: [
        new OA\Property(property: 'type', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class BarrelAttachResource extends AbstractBaseResource
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
            'type' => $this->type ?? null,
        ];
    }
}
